<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\HistorialMedico;
use App\Models\Paciente;

class HistorialMedicoSeeder extends Seeder
{
    public function run(): void
    {
        // Obtener todos los pacientes existentes
        $pacientes = Paciente::all();

        if ($pacientes->isEmpty()) {
            echo "No hay pacientes en la base de datos. Ejecuta primero UsuariosPorRolSeeder.\n";
            return;
        }

        $historialesMedicos = [
            [
                'motivo_consulta' => 'Dolor lumbar crónico después de accidente laboral',
                'alergias' => 'Alergia a ibuprofeno y aspirina',
                'medicamentos_actuales' => 'Paracetamol 500mg cada 8 horas, Complejo B diario',
                'antecedentes_familiares' => 'Madre con artritis reumatoide, padre con diabetes tipo 2',
                'cirugias_previas' => 'Apendicectomía a los 25 años',
                'lesiones_previas' => 'Fractura de muñeca derecha en 2020, esguince de tobillo en 2019',
                'inspeccion_general' => 'Postura antiálgica, marcha claudicante, contractura muscular paravertebral',
                'rango_movimiento' => 'Flexión lumbar limitada a 30°, extensión limitada a 10°, rotación bilateral limitada',
                'fuerza_muscular' => 'Fuerza 4/5 en músculos lumbares, 5/5 en extremidades',
                'pruebas_especiales' => 'Test de Lasègue positivo bilateral, Test de Schober positivo',
                'dolor_eva' => 7,
                'diagnostico_fisioterapeutico' => 'Lumbalgia mecánica con contractura muscular paravertebral',
                'frecuencia_sesiones' => '3 veces por semana durante 4 semanas',
                'tecnicas_propuestas' => 'Termoterapia, electroterapia TENS, ejercicios McKenzie, fortalecimiento core',
                'objetivos_corto_plazo' => 'Reducir dolor de 7/10 a 4/10 en 2 semanas',
                'objetivos_mediano_plazo' => 'Aumentar rango de movimiento lumbar en 50% en 1 mes',
                'objetivos_largo_plazo' => 'Retorno completo a actividades laborales sin dolor en 8 semanas',
                'observacion_general' => 'Paciente colaborativo, comprometido con el tratamiento'
            ],
            [
                'motivo_consulta' => 'Rehabilitación post-quirúrgica de rodilla derecha (meniscectomía)',
                'alergias' => 'Sin alergias conocidas',
                'medicamentos_actuales' => 'Tramadol 50mg según necesidad, Omega 3 diario',
                'antecedentes_familiares' => 'Sin antecedentes familiares relevantes',
                'cirugias_previas' => 'Meniscectomía artroscópica rodilla derecha hace 3 semanas',
                'lesiones_previas' => 'Múltiples lesiones deportivas menores en fútbol',
                'inspeccion_general' => 'Cicatriz quirúrgica bien cicatrizada, edema leve periarticular',
                'rango_movimiento' => 'Flexión de rodilla 90°, extensión completa con dolor',
                'fuerza_muscular' => 'Cuádriceps 3/5, isquiotibiales 4/5, resto normal',
                'pruebas_especiales' => 'Test de cajón anterior negativo, McMurray negativo',
                'dolor_eva' => 4,
                'diagnostico_fisioterapeutico' => 'Post-operatorio de meniscectomía con debilidad muscular',
                'frecuencia_sesiones' => 'Diario durante 2 semanas, luego 3 veces por semana',
                'tecnicas_propuestas' => 'Movilización articular, fortalecimiento progresivo, propiocepción',
                'objetivos_corto_plazo' => 'Lograr flexión completa de rodilla en 2 semanas',
                'objetivos_mediano_plazo' => 'Recuperar fuerza muscular completa en 6 semanas',
                'objetivos_largo_plazo' => 'Retorno al deporte sin limitaciones en 12 semanas',
                'observacion_general' => 'Deportista motivado, buen cumplimiento de ejercicios en casa'
            ],
            [
                'motivo_consulta' => 'Cervicalgia y cefaleas por trabajo de oficina',
                'alergias' => 'Alergia estacional (rinitis)',
                'medicamentos_actuales' => 'Relajante muscular ocasional, antihistamínico estacional',
                'antecedentes_familiares' => 'Madre con migrañas, hermana con fibromialgia',
                'cirugias_previas' => 'Ninguna',
                'lesiones_previas' => 'Latigazo cervical leve por accidente de tránsito en 2018',
                'inspeccion_general' => 'Rectificación cervical, hombros elevados, postura cifótica',
                'rango_movimiento' => 'Rotación cervical limitada bilateralmente, flexión lateral dolorosa',
                'fuerza_muscular' => 'Debilidad en flexores cervicales profundos 3/5',
                'pruebas_especiales' => 'Test de flexión craneocervical positivo, Spurling bilateral positivo',
                'dolor_eva' => 5,
                'diagnostico_fisioterapeutico' => 'Síndrome de dolor cervical con cefalea tensional',
                'frecuencia_sesiones' => '2 veces por semana durante 6 semanas',
                'tecnicas_propuestas' => 'Terapia manual cervical, ejercicios posturales, ergonomía laboral',
                'objetivos_corto_plazo' => 'Reducir frecuencia de cefaleas en 50% en 3 semanas',
                'objetivos_mediano_plazo' => 'Mejorar postura cervical y reducir tensión muscular',
                'objetivos_largo_plazo' => 'Eliminar cefaleas y mantener higiene postural laboral',
                'observacion_general' => 'Requiere modificaciones ergonómicas en el trabajo'
            ]
        ];

        foreach ($pacientes->take(3) as $index => $paciente) {
            if (isset($historialesMedicos[$index])) {
                $datosHistorial = array_merge($historialesMedicos[$index], [
                    'paciente_id' => $paciente->id,
                    'fecha_creacion' => now()->subDays(rand(7, 30)),
                ]);

                HistorialMedico::create($datosHistorial);
                echo "Creado historial médico para paciente ID: {$paciente->id}\n";
            }
        }

        // Crear historial básico para el resto de pacientes
        foreach ($pacientes->skip(3) as $paciente) {
            HistorialMedico::create([
                'paciente_id' => $paciente->id,
                'fecha_creacion' => now()->subDays(rand(1, 15)),
                'motivo_consulta' => 'Consulta general de fisioterapia',
                'observacion_general' => 'Paciente nuevo, evaluación inicial pendiente',
                'dolor_eva' => rand(2, 6),
            ]);
            echo "Creado historial básico para paciente ID: {$paciente->id}\n";
        }
    }
}