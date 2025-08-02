<?php

namespace App\Console\Commands;

use App\Models\Usuario;
use App\Models\Paciente;
use App\Models\Terapeuta;
use App\Models\Recepcionista;
use App\Models\Administrador;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class InitializeProductionDataCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'production:initialize-data 
                            {--force : Ejecutar sin confirmación}';

    /**
     * The console command description.
     */
    protected $description = 'Inicializar datos específicos de roles para usuarios existentes en producción';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('🚀 Inicializando datos específicos de roles en producción...');
        
        if (app()->environment('local') && !$this->option('force')) {
            $this->warn('⚠️ Este comando está diseñado para producción.');
            if (!$this->confirm('¿Continuar en entorno local?')) {
                return Command::SUCCESS;
            }
        }

        try {
            $this->initializeRoleSpecificData();
            $this->info('✅ Inicialización completada exitosamente.');
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error('❌ Error durante la inicialización: ' . $e->getMessage());
            Log::error('Error en inicialización de producción', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return Command::FAILURE;
        }
    }

    /**
     * Inicializar datos específicos de roles
     */
    private function initializeRoleSpecificData(): void
    {
        DB::beginTransaction();

        try {
            $stats = [
                'pacientes_creados' => 0,
                'terapeutas_creados' => 0,
                'recepcionistas_creados' => 0,
                'administradores_creados' => 0,
                'errores' => 0
            ];

            // Procesar cada rol
            $this->processRole(4, 'Pacientes', $stats);
            $this->processRole(2, 'Terapeutas', $stats);
            $this->processRole(3, 'Recepcionistas', $stats);
            $this->processRole(1, 'Administradores', $stats);

            DB::commit();

            // Mostrar estadísticas
            $this->displayStats($stats);

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Procesar usuarios de un rol específico
     */
    private function processRole(int $rolId, string $roleName, array &$stats): void
    {
        $this->info("📋 Procesando {$roleName}...");
        
        $usuarios = Usuario::where('rol_id', $rolId)->get();
        
        if ($usuarios->isEmpty()) {
            $this->line("   No hay usuarios con rol {$roleName}");
            return;
        }

        $bar = $this->output->createProgressBar($usuarios->count());
        $bar->start();

        foreach ($usuarios as $usuario) {
            try {
                $created = $this->createRoleSpecificRecord($usuario, $rolId);
                if ($created) {
                    $stats[strtolower($roleName) . '_creados']++;
                }
            } catch (\Exception $e) {
                $stats['errores']++;
                Log::warning("Error al procesar usuario {$usuario->id}", [
                    'error' => $e->getMessage(),
                    'usuario_id' => $usuario->id,
                    'rol_id' => $rolId
                ]);
            }
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
    }

    /**
     * Crear registro específico si no existe
     */
    private function createRoleSpecificRecord(Usuario $usuario, int $rolId): bool
    {
        switch ($rolId) {
            case 4: // Paciente
                if (!Paciente::find($usuario->id)) {
                    DB::table('pacientes')->insert([
                        'id' => $usuario->id,
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                    return true;
                }
                break;

            case 2: // Terapeuta
                if (!Terapeuta::find($usuario->id)) {
                    DB::table('terapeutas')->insert([
                        'id' => $usuario->id,
                        'estatus' => 'activo',
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                    return true;
                }
                break;

            case 3: // Recepcionista
                if (!Recepcionista::find($usuario->id)) {
                    DB::table('recepcionistas')->insert([
                        'id' => $usuario->id,
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                    return true;
                }
                break;

            case 1: // Administrador
                if (!Administrador::find($usuario->id)) {
                    DB::table('administradores')->insert([
                        'id' => $usuario->id,
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                    return true;
                }
                break;
        }

        return false;
    }

    /**
     * Mostrar estadísticas del proceso
     */
    private function displayStats(array $stats): void
    {
        $this->newLine();
        $this->info('📊 Estadísticas del proceso:');
        $this->table(
            ['Tipo', 'Registros Creados'],
            [
                ['Pacientes', $stats['pacientes_creados']],
                ['Terapeutas', $stats['terapeutas_creados']],
                ['Recepcionistas', $stats['recepcionistas_creados']],
                ['Administradores', $stats['administradores_creados']],
                ['Errores', $stats['errores']],
            ]
        );

        $total = $stats['pacientes_creados'] + $stats['terapeutas_creados'] + 
                $stats['recepcionistas_creados'] + $stats['administradores_creados'];

        if ($total > 0) {
            $this->info("✅ Total de registros creados: {$total}");
        } else {
            $this->info("ℹ️ Todos los registros ya existían.");
        }

        if ($stats['errores'] > 0) {
            $this->warn("⚠️ Se encontraron {$stats['errores']} errores. Revisar logs para detalles.");
        }
    }
}