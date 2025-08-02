<?php

namespace App\Console\Commands;

use App\Models\Usuario;
use App\Models\Paciente;
use App\Models\Terapeuta;
use App\Models\Recepcionista;
use App\Models\Administrador;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class FixRoleDataConsistencyCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'fix:role-data-consistency 
                            {--dry-run : Solo mostrar qué se haría sin ejecutar cambios}
                            {--role= : Solo corregir un rol específico (1,2,3,4)}';

    /**
     * The console command description.
     */
    protected $description = 'Corrige las inconsistencias entre usuarios y sus registros específicos de rol';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $dryRun = $this->option('dry-run');
        $specificRole = $this->option('role');

        $this->info('🔍 Analizando inconsistencias en roles y datos específicos...');
        
        if ($dryRun) {
            $this->warn('⚠️ MODO DRY-RUN: Solo se mostrarán los cambios, no se ejecutarán.');
        }

        $issues = $this->analyzeInconsistencies($specificRole);
        
        if (empty($issues)) {
            $this->info('✅ No se encontraron inconsistencias.');
            return Command::SUCCESS;
        }

        $this->displayIssues($issues);

        if (!$dryRun) {
            if ($this->confirm('¿Deseas proceder con las correcciones?')) {
                $this->fixInconsistencies($issues);
            } else {
                $this->info('❌ Operación cancelada.');
                return Command::SUCCESS;
            }
        }

        return Command::SUCCESS;
    }

    /**
     * Analizar inconsistencias en la base de datos
     */
    private function analyzeInconsistencies(?string $specificRole = null): array
    {
        $issues = [];

        // Definir roles a verificar
        $rolesToCheck = $specificRole ? [$specificRole] : [1, 2, 3, 4];

        foreach ($rolesToCheck as $rolId) {
            $roleName = $this->getRoleName($rolId);
            $this->info("📋 Verificando rol: {$roleName} (ID: {$rolId})");

            $users = Usuario::where('rol_id', $rolId)->get();
            
            foreach ($users as $user) {
                $issue = $this->checkUserRoleConsistency($user);
                if ($issue) {
                    $issues[] = $issue;
                }
            }
        }

        return $issues;
    }

    /**
     * Verificar consistencia de un usuario específico
     */
    private function checkUserRoleConsistency(Usuario $user): ?array
    {
        $roleName = $this->getRoleName($user->rol_id);
        
        switch ($user->rol_id) {
            case 4: // Paciente
                if (!$user->paciente) {
                    return [
                        'type' => 'missing_role_record',
                        'user_id' => $user->id,
                        'user_email' => $user->correo_electronico,
                        'role_id' => $user->rol_id,
                        'role_name' => $roleName,
                        'issue' => 'Usuario con rol Paciente sin registro en tabla pacientes'
                    ];
                }
                break;

            case 2: // Terapeuta
                if (!$user->terapeuta) {
                    return [
                        'type' => 'missing_role_record',
                        'user_id' => $user->id,
                        'user_email' => $user->correo_electronico,
                        'role_id' => $user->rol_id,
                        'role_name' => $roleName,
                        'issue' => 'Usuario con rol Terapeuta sin registro en tabla terapeutas'
                    ];
                }
                break;

            case 3: // Recepcionista
                if (!$user->recepcionista) {
                    return [
                        'type' => 'missing_role_record',
                        'user_id' => $user->id,
                        'user_email' => $user->correo_electronico,
                        'role_id' => $user->rol_id,
                        'role_name' => $roleName,
                        'issue' => 'Usuario con rol Recepcionista sin registro en tabla recepcionistas'
                    ];
                }
                break;

            case 1: // Administrador
                if (!$user->administrador) {
                    return [
                        'type' => 'missing_role_record',
                        'user_id' => $user->id,
                        'user_email' => $user->correo_electronico,
                        'role_id' => $user->rol_id,
                        'role_name' => $roleName,
                        'issue' => 'Usuario con rol Administrador sin registro en tabla administradores'
                    ];
                }
                break;
        }

        return null;
    }

    /**
     * Mostrar los problemas encontrados
     */
    private function displayIssues(array $issues): void
    {
        $this->error('❌ Se encontraron ' . count($issues) . ' inconsistencias:');
        $this->newLine();

        $groupedIssues = [];
        foreach ($issues as $issue) {
            $groupedIssues[$issue['role_name']][] = $issue;
        }

        foreach ($groupedIssues as $roleName => $roleIssues) {
            $this->warn("🔸 {$roleName} ({" . count($roleIssues) . " problemas):");
            
            foreach ($roleIssues as $issue) {
                $this->line("  - Usuario ID {$issue['user_id']} ({$issue['user_email']}): {$issue['issue']}");
            }
            $this->newLine();
        }
    }

    /**
     * Corregir las inconsistencias encontradas
     */
    private function fixInconsistencies(array $issues): void
    {
        $this->info('🔧 Iniciando correcciones...');
        
        DB::beginTransaction();
        
        try {
            $fixed = 0;
            $errors = 0;

            foreach ($issues as $issue) {
                try {
                    $this->fixSingleIssue($issue);
                    $fixed++;
                    $this->line("✅ Corregido: Usuario {$issue['user_id']} - {$issue['role_name']}");
                } catch (\Exception $e) {
                    $errors++;
                    $this->error("❌ Error al corregir Usuario {$issue['user_id']}: {$e->getMessage()}");
                }
            }

            if ($errors === 0) {
                DB::commit();
                $this->info("🎉 Todas las correcciones completadas exitosamente. Total: {$fixed}");
            } else {
                DB::rollBack();
                $this->error("❌ Se encontraron {$errors} errores. Cambios deshechos.");
                return;
            }

        } catch (\Exception $e) {
            DB::rollBack();
            $this->error("❌ Error general: {$e->getMessage()}");
        }
    }

    /**
     * Corregir un problema específico
     */
    private function fixSingleIssue(array $issue): void
    {
        $user = Usuario::findOrFail($issue['user_id']);

        switch ($issue['role_id']) {
            case 4: // Paciente
                Paciente::create(['id' => $user->id]);
                break;

            case 2: // Terapeuta
                Terapeuta::create([
                    'id' => $user->id,
                    'estatus' => 'activo'
                ]);
                break;

            case 3: // Recepcionista
                Recepcionista::create(['id' => $user->id]);
                break;

            case 1: // Administrador
                Administrador::create(['id' => $user->id]);
                break;

            default:
                throw new \InvalidArgumentException("Rol no válido: {$issue['role_id']}");
        }
    }

    /**
     * Obtener el nombre del rol
     */
    private function getRoleName(int $rolId): string
    {
        return match ($rolId) {
            1 => 'Administrador',
            2 => 'Terapeuta', 
            3 => 'Recepcionista',
            4 => 'Paciente',
            default => 'Desconocido'
        };
    }
}