<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Usuario;
use App\Models\Paciente;
use App\Models\Terapeuta;
use App\Models\Tratamiento;
use App\Models\Cita;
use App\Models\HistorialMedico;
use App\Models\Registro;
use App\Models\Pago;
use App\Models\Encuesta;
use App\Models\Pregunta;
use App\Models\Respuesta;
use Carbon\Carbon;

class DatosPruebaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Verificar que existan usuarios de prueba
        $pacienteUser = Usuario::where('correo_electronico', 'paciente@test.com')->first();
        $terapeutaUser = Usuario::where('correo_electronico', 'doctor@test.com')->first();
        
        if (!$pacienteUser || !$terapeutaUser) {
            $this->command->error('Usuarios de prueba no encontrados. Ejecuta primero el seeder de usuarios.');
            return;
        }

        // Obtener paciente y terapeuta
        $paciente = $pacienteUser->paciente;
        $terapeuta = $terapeutaUser->terapeuta;

        if (!$paciente || !$terapeuta) {
            $this->command->error('Paciente o terapeuta no encontrados.');
            return;
        }

        // Crear tratamientos de prueba
        $tratamientos = [
            Tratamiento::firstOrCreate(['titulo' => 'Fisioterapia General'], [
                'descripcion' => 'Tratamiento de fisioterapia general',
                'duracion' => 60,
                'frecuencia' => 'Semanal'
            ]),
            Tratamiento::firstOrCreate(['titulo' => 'Rehabilitación Lumbar'], [
                'descripcion' => 'Tratamiento especializado para problemas lumbares',
                'duracion' => 60,
                'frecuencia' => 'Bisemanal'
            ]),
            Tratamiento::firstOrCreate(['titulo' => 'Terapia Manual'], [
                'descripcion' => 'Terapia manual especializada',
                'duracion' => 45,
                'frecuencia' => 'Semanal'
            ]),
            Tratamiento::firstOrCreate(['titulo' => 'Evaluación Inicial'], [
                'descripcion' => 'Evaluación completa del paciente',
                'duracion' => 90,
                'frecuencia' => 'Única'
            ])
        ];

        // Crear historial médico
        $historialMedico = HistorialMedico::firstOrCreate([
            'paciente_id' => $paciente->id
        ], [
            'observacion_general' => 'Paciente con historial de dolor lumbar crónico. Presenta buena respuesta al tratamiento.',
            'fecha_creacion' => Carbon::now()->subMonths(3)
        ]);

        // Crear citas de prueba con diferentes estados
        $fechas = [
            Carbon::now()->subDays(30),
            Carbon::now()->subDays(15),
            Carbon::now()->subDays(7),
            Carbon::now()->subDays(3),
            Carbon::now()->addDays(5),
            Carbon::now()->addDays(10)
        ];

        $estados = ['atendida', 'atendida', 'atendida', 'atendida', 'agendada', 'agendada'];
        $citasCreadas = [];

        foreach ($fechas as $index => $fecha) {
            $cita = Cita::create([
                'fecha_hora' => $fecha,
                'duracion' => 60,
                'estado' => $estados[$index],
                'tipo' => 'consulta',
                'ubicacion' => 'Consultorio ' . ($index % 3 + 1),
                'equipo_asignado' => 'Camilla ' . ($index % 2 + 1),
                'motivo' => 'Dolor lumbar',
                'observaciones' => $estados[$index] === 'atendida' ? 'Sesión completada exitosamente. Paciente muestra mejoría en movilidad.' : null,
                'escala_dolor_eva_inicio' => $estados[$index] === 'atendida' ? rand(3, 8) : null,
                'escala_dolor_eva_fin' => $estados[$index] === 'atendida' ? rand(1, 4) : null,
                'como_fue_lesion' => $estados[$index] === 'atendida' ? 'Dolor lumbar mecánico por malas posturas laborales' : null,
                'antecedentes_patologicos' => $estados[$index] === 'atendida' ? 'Episodios previos de lumbago, hernia discal L4-L5' : null,
                'antecedentes_no_patologicos' => $estados[$index] === 'atendida' ? 'Trabajo de oficina, sedentarismo, estrés laboral' : null,
                'paciente_id' => $paciente->id,
                'terapeuta_id' => $terapeuta->id,
                'tratamiento_id' => $tratamientos[array_rand($tratamientos)]->id,
                'created_at' => $fecha,
                'updated_at' => $fecha
            ]);

            $citasCreadas[] = $cita;
        }

        // Crear registros del historial médico
        foreach ($citasCreadas as $index => $cita) {
            if ($cita->estado === 'atendida') {
                Registro::create([
                    'historial_medico_id' => $historialMedico->id,
                    'fecha_hora' => $cita->fecha_hora,
                    'antecedentes' => $cita->antecedentes_patologicos,
                    'medicacion_actual' => 'Ibuprofeno 400mg, Paracetamol 500mg',
                    'postura' => 'Cifosis dorsal leve, anteversión pélvica',
                    'marcha' => 'Marcha antálgica, paso corto',
                    'fuerza_muscular' => 'Debilidad en glúteos y abdominales',
                    'amplitud_movimiento' => 'Limitación en flexión lumbar',
                    'pruebas_especiales' => 'Lasegue negativo, Bragart positivo',
                    'palpacion' => 'Contractura paravertebral L3-L5',
                    'localizacion_dolor' => 'Región lumbar baja',
                    'intensidad_dolor' => $cita->escala_dolor_eva_inicio,
                    'tipo_dolor' => 'Mecánico, punzante',
                    'factores_desencadenantes' => 'Sedestación prolongada, flexión',
                    'factores_alivio' => 'Reposo, calor local',
                    'observaciones' => $cita->observaciones,
                    'numero_sesion' => $index + 1
                ]);
            }
        }

        // Crear pagos para las citas atendidas
        foreach ($citasCreadas as $cita) {
            if ($cita->estado === 'atendida') {
                Pago::create([
                    'fecha_hora' => $cita->fecha_hora->addHours(1),
                    'monto' => $cita->tratamiento->precio,
                    'forma_pago' => ['efectivo', 'transferencia', 'terminal'][array_rand(['efectivo', 'transferencia', 'terminal'])],
                    'recibo' => 'R-' . date('Y') . '-' . str_pad($cita->id, 4, '0', STR_PAD_LEFT),
                    'cita_id' => $cita->id,
                    'autorizacion' => 'AUTH-' . rand(100000, 999999),
                    'factura_emitida' => rand(0, 1) ? true : false
                ]);
            }
        }

        // Crear encuesta de satisfacción
        $encuesta = Encuesta::firstOrCreate([
            'titulo' => 'Encuesta de Satisfacción del Paciente'
        ], [
            'tipo' => 'satisfaccion',
            'fecha_creacion' => Carbon::now()->subMonths(2),
            'recepcionista_id' => 1 // Asumiendo que hay un recepcionista con ID 1
        ]);

        // Crear preguntas para la encuesta
        $preguntas = [
            '¿Cómo calificarías la atención recibida?',
            '¿El terapeuta explicó claramente tu diagnóstico?',
            '¿Te sientes satisfecho con el tratamiento?',
            'Comentarios adicionales'
        ];

        foreach ($preguntas as $index => $textoPregunta) {
            Pregunta::firstOrCreate([
                'encuesta_id' => $encuesta->id,
                'texto' => $textoPregunta
            ]);
        }

        // Crear respuestas para algunas citas (simular que el paciente respondió algunas encuestas)
        $citasParaResponder = collect($citasCreadas)->where('estado', 'atendida')->take(2);
        
        foreach ($citasParaResponder as $cita) {
            // Responder las primeras 3 preguntas con rating
            for ($i = 1; $i <= 3; $i++) {
                Respuesta::create([
                    'texto' => rand(4, 5), // Calificaciones altas
                    'tipo' => 'rating',
                    'pregunta_id' => $i,
                    'paciente_id' => $paciente->id,
                    'cita_id' => $cita->id,
                    'fecha_respuesta' => $cita->fecha_hora->addDays(1)
                ]);
            }

            // Responder la pregunta de texto
            Respuesta::create([
                'texto' => 'Excelente atención, muy profesional. Me siento mucho mejor después del tratamiento.',
                'tipo' => 'texto',
                'pregunta_id' => 4,
                'paciente_id' => $paciente->id,
                'cita_id' => $cita->id,
                'fecha_respuesta' => $cita->fecha_hora->addDays(1)
            ]);
        }

        $this->command->info('Datos de prueba creados exitosamente:');
        $this->command->info('- Historial médico: 1 registro');
        $this->command->info('- Citas: ' . count($citasCreadas) . ' registros');
        $this->command->info('- Registros médicos: ' . count($citasCreadas->where('estado', 'atendida')) . ' registros');
        $this->command->info('- Pagos: ' . count($citasCreadas->where('estado', 'atendida')) . ' registros');
        $this->command->info('- Encuestas respondidas: ' . $citasParaResponder->count() . ' registros');
        $this->command->info('- Tratamientos: ' . count($tratamientos) . ' registros');
    }
}