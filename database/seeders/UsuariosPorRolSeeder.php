<?php

namespace Database\Seeders;

use App\Models\Administrador;
use App\Models\Clinica;
use App\Models\Paciente;
use App\Models\Recepcionista;
use App\Models\Terapeuta;
use App\Models\Usuario;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UsuariosPorRolSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Limpiar la tabla de administradores
        $this->command->info('🌱 Limpiando la tabla de administradores...');

        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('administradores')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // Limpiar la tabla antes de insertar (¡OJO con las foreign keys!)
        $this->command->info('🌱 Limpiando la tabla de usuarios...');

        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('usuarios')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // Limpiar la tabla de terapeutas
        $this->command->info('🌱 Limpiando la tabla de terapeutas...');

        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('terapeutas')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // Limpiar la tabla de recepcionistas
        $this->command->info('🌱 Limpiando la tabla de recepcionistas...');

        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('recepcionistas')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $this->command->info('🌱 Creando usuarios por rol...');

        // Crear clínica básica si no existe
        $clinica = Clinica::firstOrCreate(
            ['id' => 1],
            [
                'nombre' => 'PhisyoMar - Clínica Principal',
                'direccion' => 'Av. Reforma 123, Col. Centro, CDMX',
                'razon_social' => 'PhisyoMar S.A. de C.V.',
                'rfc' => 'RFC123456ABC',
                'no_licencia_sanitaria' => 'LIC123456',
                'no_registro_patronal' => 'REG987654',
                'no_aviso_de_funcionamiento' => 'AVF456789',
                'colores_corporativos' => '#00BFFF',
                'logo_url' => 'http://logo.com/logo.png',
            ]
        );

        // === 1. ADMINISTRADOR (ROL ID: 1) ===
        $this->command->info('👑 Creando Administrador...');

        $adminUsuario = Usuario::factory()->administrador()->create([
            'nombre' => 'Dr. Carlos',
            'apellido_paterno' => 'Administrador',
            'apellido_materno' => 'Sistema',
            'correo_electronico' => 'admin@phisyomar.com',
        ]);

        $admin = Administrador::factory()->create([
            'id' => $adminUsuario->id,
            'clinica_id' => $clinica->id,
        ]);

        $this->command->info("✅ Administrador creado: {$adminUsuario->correo_electronico}");

        // === 2. TERAPEUTA (ROL ID: 2) ===
        $this->command->info('🩺 Creando Terapeuta...');

        $terapeutaUsuario = Usuario::factory()->terapeuta()->create([
            'nombre' => 'Dra. María',
            'apellido_paterno' => 'Fisioterapeuta',
            'apellido_materno' => 'Especialista',
        ]);

        $terapeuta = Terapeuta::factory()->create([
            'id' => $terapeutaUsuario->id,
            'especialidad_principal' => 'Fisioterapia Deportiva',
            'experiencia_anios' => 8,
        ]);

        $this->command->info("✅ Terapeuta creado: {$terapeutaUsuario->correo_electronico}");

        // === 3. RECEPCIONISTA (ROL ID: 3) ===
        $this->command->info('📋 Creando Recepcionista...');

        $recepcionistaUsuario = Usuario::factory()->recepcionista()->create([
            'nombre' => 'Ana',
            'apellido_paterno' => 'Recepción',
            'apellido_materno' => 'Coordinadora',
        ]);

        $recepcionista = Recepcionista::factory()->create([
            'id' => $recepcionistaUsuario->id,
        ]);

        $this->command->info("✅ Recepcionista creada: {$recepcionistaUsuario->correo_electronico}");

        // === 4. PACIENTE (ROL ID: 4) ===
        $this->command->info('🏥 Creando Paciente...');

        $pacienteUsuario = Usuario::factory()->paciente()->create([
            'nombre' => 'Luis',
            'apellido_paterno' => 'Paciente',
            'apellido_materno' => 'Ejemplo',
            'correo_electronico' => 'paciente@gmail.com',
        ]);

        $paciente = Paciente::factory()->create([
            'id' => $pacienteUsuario->id,
            'contacto_emergencia_nombre' => 'María Paciente Familiar',
            'contacto_emergencia_telefono' => '5551234567',
            'contacto_emergencia_parentesco' => 'Esposa',
        ]);

        $this->command->info("✅ Paciente creado: {$pacienteUsuario->correo_electronico}");

        // === RESUMEN ===
        $this->command->info('');
        $this->command->info('🎉 ¡Usuarios creados exitosamente!');
        $this->command->info('');
        $this->command->info('📋 CREDENCIALES DE ACCESO:');
        $this->command->info('  👑 Administrador: admin@phisyomar.com | Password123!');
        $this->command->info('  🩺 Terapeuta: '.$terapeutaUsuario->correo_electronico.' | Password123!');
        $this->command->info('  📋 Recepcionista: '.$recepcionistaUsuario->correo_electronico.' | Password123!');
        $this->command->info('  🏥 Paciente: paciente@gmail.com | Password123!');
        $this->command->info('');
        $this->command->info('🔐 ROLES ASIGNADOS:');
        $this->command->info('  ID 1: Administrador - Acceso total');
        $this->command->info('  ID 2: Terapeuta - Pacientes asignados');
        $this->command->info('  ID 3: Recepcionista - Gestión operativa');
        $this->command->info('  ID 4: Paciente - Solo información personal');
    }
}
