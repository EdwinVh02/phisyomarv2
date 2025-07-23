<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Especialidad;
use App\Models\Usuario;
use App\Models\Terapeuta;
use App\Models\Clinica;
use App\Models\Recepcionista;

class DatosRealesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('=== INSERTANDO ESPECIALIDADES REALES ===');
        
        // Especialidades reales de fisioterapia
        $especialidades = [
            ['nombre' => 'Fisioterapia Deportiva'],
            ['nombre' => 'Fisioterapia Neurológica'],
            ['nombre' => 'Fisioterapia Ortopédica'],
            ['nombre' => 'Fisioterapia Pediátrica'],
            ['nombre' => 'Fisioterapia Respiratoria'],
            ['nombre' => 'Fisioterapia Geriátrica'],
            ['nombre' => 'Terapia Manual']
        ];

        foreach($especialidades as $esp) {
            $especialidad = Especialidad::firstOrCreate($esp);
            if ($especialidad->wasRecentlyCreated) {
                $this->command->info("✓ Creada: {$especialidad->nombre}");
            } else {
                $this->command->info("◦ Ya existe: {$especialidad->nombre}");
            }
        }

        $this->command->info('=== INSERTANDO TERAPEUTAS REALES ===');
        
        // Datos reales de terapeutas mexicanos
        $terapeutas_data = [
            [
                'usuario' => [
                    'nombre' => 'Ana Patricia',
                    'apellido_paterno' => 'Hernández',
                    'apellido_materno' => 'López',
                    'correo_electronico' => 'ana.hernandez@phisyomar.com',
                    'contraseña' => 'terapeuta123',
                    'telefono' => '(669) 123-4567',
                    'direccion' => 'Av. Insurgentes 456, Col. Del Valle, Mazatlán, Sinaloa',
                    'fecha_nacimiento' => '1985-03-15',
                    'sexo' => 'Femenino',
                    'curp' => 'HELA850315MSRNPN03',
                    'estatus' => 'activo',
                    'rol_id' => 2
                ],
                'terapeuta' => [
                    'cedula_profesional' => '7654321',
                    'especialidad_principal' => 'Fisioterapia Deportiva',
                    'experiencia_anios' => 8,
                    'estatus' => 'activo'
                ]
            ],
            [
                'usuario' => [
                    'nombre' => 'Carlos Alberto',
                    'apellido_paterno' => 'Rodríguez',
                    'apellido_materno' => 'Morales',
                    'correo_electronico' => 'carlos.rodriguez@phisyomar.com',
                    'contraseña' => 'terapeuta123',
                    'telefono' => '(669) 234-5678',
                    'direccion' => 'Calle Revolución 789, Col. Centro, Mazatlán, Sinaloa',
                    'fecha_nacimiento' => '1982-07-22',
                    'sexo' => 'Masculino',
                    'curp' => 'ROMC820722HSLDRR08',
                    'estatus' => 'activo',
                    'rol_id' => 2
                ],
                'terapeuta' => [
                    'cedula_profesional' => '8765432',
                    'especialidad_principal' => 'Fisioterapia Neurológica',
                    'experiencia_anios' => 12,
                    'estatus' => 'activo'
                ]
            ],
            [
                'usuario' => [
                    'nombre' => 'María Elena',
                    'apellido_paterno' => 'García',
                    'apellido_materno' => 'Sánchez',
                    'correo_electronico' => 'maria.garcia@phisyomar.com',
                    'contraseña' => 'terapeuta123',
                    'telefono' => '(669) 345-6789',
                    'direccion' => 'Av. Universidad 321, Col. Universidad, Mazatlán, Sinaloa',
                    'fecha_nacimiento' => '1990-11-08',
                    'sexo' => 'Femenino',
                    'curp' => 'GASM901108MSLRNR02',
                    'estatus' => 'activo',
                    'rol_id' => 2
                ],
                'terapeuta' => [
                    'cedula_profesional' => '9876543',
                    'especialidad_principal' => 'Fisioterapia Pediátrica',
                    'experiencia_anios' => 5,
                    'estatus' => 'activo'
                ]
            ],
            [
                'usuario' => [
                    'nombre' => 'José Luis',
                    'apellido_paterno' => 'Martínez',
                    'apellido_materno' => 'Flores',
                    'correo_electronico' => 'jose.martinez@phisyomar.com',
                    'contraseña' => 'terapeuta123',
                    'telefono' => '(669) 456-7890',
                    'direccion' => 'Calle Hidalgo 654, Col. Centro Histórico, Mazatlán, Sinaloa',
                    'fecha_nacimiento' => '1987-09-14',
                    'sexo' => 'Masculino',
                    'curp' => 'MAFL870914HSLRLR01',
                    'estatus' => 'activo',
                    'rol_id' => 2
                ],
                'terapeuta' => [
                    'cedula_profesional' => '1234567',
                    'especialidad_principal' => 'Fisioterapia Respiratoria',
                    'experiencia_anios' => 10,
                    'estatus' => 'activo'
                ]
            ],
            [
                'usuario' => [
                    'nombre' => 'Lucía',
                    'apellido_paterno' => 'Ramírez',
                    'apellido_materno' => 'Torres',
                    'correo_electronico' => 'lucia.ramirez@phisyomar.com',
                    'contraseña' => 'terapeuta123',
                    'telefono' => '(669) 567-8901',
                    'direccion' => 'Av. Ejército Mexicano 987, Col. Tellería, Mazatlán, Sinaloa',
                    'fecha_nacimiento' => '1983-12-03',
                    'sexo' => 'Femenino',
                    'curp' => 'RATL831203MSLMRC05',
                    'estatus' => 'activo',
                    'rol_id' => 2
                ],
                'terapeuta' => [
                    'cedula_profesional' => '2345678',
                    'especialidad_principal' => 'Fisioterapia Geriátrica',
                    'experiencia_anios' => 15,
                    'estatus' => 'activo'
                ]
            ]
        ];

        foreach($terapeutas_data as $data) {
            // Verificar si el usuario ya existe
            $existeUsuario = Usuario::where('correo_electronico', $data['usuario']['correo_electronico'])->first();
            if ($existeUsuario) {
                $this->command->info("◦ Usuario ya existe: {$data['usuario']['correo_electronico']}");
                continue;
            }
            
            // Crear usuario
            $usuario = Usuario::create($data['usuario']);
            $this->command->info("✓ Usuario creado: {$usuario->nombre} {$usuario->apellido_paterno}");
            
            // Crear terapeuta
            $terapeutaData = $data['terapeuta'];
            $terapeutaData['id'] = $usuario->id; // El ID del terapeuta es el mismo que el usuario
            $terapeuta = Terapeuta::create($terapeutaData);
            $this->command->info("  ✓ Terapeuta creado con cédula: {$terapeuta->cedula_profesional}");
        }

        $this->command->info('=== INSERTANDO CLÍNICAS ADICIONALES ===');
        
        // Crear más clínicas
        $clinicas = [
            [
                'nombre' => 'PhisyoMar - Sucursal Norte',
                'direccion' => 'Av. Rafael Buelna 1234, Col. Flamingos, Mazatlán, Sinaloa',
                'razon_social' => 'PhisyoMar Norte S.A. de C.V.',
                'rfc' => 'PMN240719ABC',
                'no_licencia_sanitaria' => 'LIC789012',
                'no_registro_patronal' => 'REG567890',
                'no_aviso_de_funcionamiento' => 'AVF123890',
                'colores_corporativos' => '#0066CC',
                'logo_url' => 'https://phisyomar.com/logos/norte.png'
            ],
            [
                'nombre' => 'PhisyoMar - Sucursal Sur',
                'direccion' => 'Av. del Mar 567, Col. Olas Altas, Mazatlán, Sinaloa',
                'razon_social' => 'PhisyoMar Sur S.A. de C.V.',
                'rfc' => 'PMS240719DEF',
                'no_licencia_sanitaria' => 'LIC345678',
                'no_registro_patronal' => 'REG234567',
                'no_aviso_de_funcionamiento' => 'AVF567234',
                'colores_corporativos' => '#0080FF',
                'logo_url' => 'https://phisyomar.com/logos/sur.png'
            ]
        ];

        foreach($clinicas as $clinica) {
            $nuevaClinica = Clinica::create($clinica);
            $this->command->info("✓ Clínica creada: {$nuevaClinica->nombre}");
        }

        $this->command->info('=== INSERTANDO RECEPCIONISTA ===');
        
        // Crear recepcionista
        $recepcionista_data = [
            'usuario' => [
                'nombre' => 'Sandra',
                'apellido_paterno' => 'Moreno',
                'apellido_materno' => 'Castillo',
                'correo_electronico' => 'sandra.moreno@phisyomar.com',
                'contraseña' => 'recepcionista123',
                'telefono' => '(669) 678-9012',
                'direccion' => 'Calle 5 de Mayo 234, Col. Centro, Mazatlán, Sinaloa',
                'fecha_nacimiento' => '1992-05-20',
                'sexo' => 'Femenino',
                'curp' => 'MOCS920520MSLRSD07',
                'estatus' => 'activo',
                'rol_id' => 3
            ],
            'recepcionista' => []
        ];

        // Verificar si el recepcionista ya existe
        $existeRecep = Usuario::where('correo_electronico', $recepcionista_data['usuario']['correo_electronico'])->first();
        if (!$existeRecep) {
            $usuario_recep = Usuario::create($recepcionista_data['usuario']);
            $this->command->info("✓ Usuario recepcionista creado: {$usuario_recep->nombre} {$usuario_recep->apellido_paterno}");

            $recepData = $recepcionista_data['recepcionista'];
            $recepData['id'] = $usuario_recep->id; // El ID del recepcionista es el mismo que el usuario
            $recepcionista = Recepcionista::create($recepData);
            $this->command->info("  ✓ Recepcionista asignada a clínica ID: {$recepcionista->clinica_id}");
        } else {
            $this->command->info("◦ Recepcionista ya existe: {$recepcionista_data['usuario']['correo_electronico']}");
        }

        $this->command->info('=== DATOS INSERTADOS EXITOSAMENTE ===');
        $this->command->info('Total especialidades: ' . Especialidad::count());
        $this->command->info('Total terapeutas: ' . Terapeuta::count());
        $this->command->info('Total clínicas: ' . Clinica::count());
        $this->command->info('Total usuarios: ' . Usuario::count());
    }
}
