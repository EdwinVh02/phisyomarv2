<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PaqueteSesion;
use App\Models\Tarifa;

class PaquetesSeeder extends Seeder
{
    public function run(): void
    {
        // First, create comprehensive tarifas for different types of treatments
        $tarifas = [
            [
                'titulo' => 'Fisioterapia General',
                'precio' => 450.00,
                'tipo' => 'General',
                'condiciones' => 'Incluye evaluación inicial, tratamiento y plan de ejercicios'
            ],
            [
                'titulo' => 'Fisioterapia Deportiva',
                'precio' => 550.00,
                'tipo' => 'Especializada',
                'condiciones' => 'Especializada en lesiones deportivas y rendimiento atlético'
            ],
            [
                'titulo' => 'Rehabilitación Neurológica',
                'precio' => 650.00,
                'tipo' => 'Especializada',
                'condiciones' => 'Para pacientes con afecciones neurológicas, incluye técnicas especializadas'
            ],
            [
                'titulo' => 'Terapia Geriátrica',
                'precio' => 400.00,
                'tipo' => 'Reducida',
                'condiciones' => 'Adaptada para adultos mayores, enfoque en movilidad y calidad de vida'
            ],
            [
                'titulo' => 'Fisioterapia Pediátrica',
                'precio' => 500.00,
                'tipo' => 'Especializada',
                'condiciones' => 'Especializada en niños y adolescentes'
            ],
            [
                'titulo' => 'Terapia de Dolor Crónico',
                'precio' => 600.00,
                'tipo' => 'Especializada',
                'condiciones' => 'Manejo integral del dolor crónico con técnicas avanzadas'
            ],
            [
                'titulo' => 'Fisioterapia Respiratoria',
                'precio' => 520.00,
                'tipo' => 'Especializada',
                'condiciones' => 'Para mejorar función pulmonar y respiratoria'
            ],
            [
                'titulo' => 'Rehabilitación Post-Quirúrgica',
                'precio' => 580.00,
                'tipo' => 'General',
                'condiciones' => 'Recuperación después de cirugías ortopédicas'
            ]
        ];

        foreach ($tarifas as $tarifa) {
            Tarifa::create($tarifa);
        }

        // Create comprehensive treatment packages
        $paquetes = [
            // Paquetes de Fisioterapia General
            [
                'nombre' => 'Paquete Básico - Fisioterapia General',
                'numero_sesiones' => 4,
                'precio' => 1620.00, // 10% descuento
                'tipo_terapia' => 'Fisioterapia General',
                'especifico_enfermedad' => 'Dolor muscular, contracturas, movilidad reducida'
            ],
            [
                'nombre' => 'Paquete Estándar - Fisioterapia General',
                'numero_sesiones' => 8,
                'precio' => 3060.00, // 15% descuento
                'tipo_terapia' => 'Fisioterapia General',
                'especifico_enfermedad' => 'Lesiones leves a moderadas, rehabilitación general'
            ],
            [
                'nombre' => 'Paquete Completo - Fisioterapia General',
                'numero_sesiones' => 12,
                'precio' => 4320.00, // 20% descuento
                'tipo_terapia' => 'Fisioterapia General',
                'especifico_enfermedad' => 'Rehabilitación integral, tratamiento completo'
            ],

            // Paquetes de Fisioterapia Deportiva
            [
                'nombre' => 'Paquete Deportivo Básico',
                'numero_sesiones' => 6,
                'precio' => 2970.00, // 10% descuento
                'tipo_terapia' => 'Fisioterapia Deportiva',
                'especifico_enfermedad' => 'Lesiones deportivas leves, prevención'
            ],
            [
                'nombre' => 'Paquete Deportivo Avanzado',
                'numero_sesiones' => 10,
                'precio' => 4675.00, // 15% descuento
                'tipo_terapia' => 'Fisioterapia Deportiva',
                'especifico_enfermedad' => 'Lesiones deportivas complejas, vuelta al deporte'
            ],
            [
                'nombre' => 'Paquete Alto Rendimiento',
                'numero_sesiones' => 15,
                'precio' => 6600.00, // 20% descuento
                'tipo_terapia' => 'Fisioterapia Deportiva',
                'especifico_enfermedad' => 'Optimización del rendimiento, prevención avanzada'
            ],

            // Paquetes de Rehabilitación Neurológica
            [
                'nombre' => 'Paquete Neurológico Inicial',
                'numero_sesiones' => 8,
                'precio' => 4680.00, // 10% descuento
                'tipo_terapia' => 'Rehabilitación Neurológica',
                'especifico_enfermedad' => 'ACV, Parkinson, esclerosis múltiple - fase inicial'
            ],
            [
                'nombre' => 'Paquete Neurológico Intensivo',
                'numero_sesiones' => 16,
                'precio' => 8320.00, // 20% descuento
                'tipo_terapia' => 'Rehabilitación Neurológica',
                'especifico_enfermedad' => 'Rehabilitación neurológica intensiva y continua'
            ],

            // Paquetes Geriátricos
            [
                'nombre' => 'Paquete Adulto Mayor Básico',
                'numero_sesiones' => 6,
                'precio' => 2040.00, // 15% descuento
                'tipo_terapia' => 'Terapia Geriátrica',
                'especifico_enfermedad' => 'Mantenimiento de movilidad, prevención de caídas'
            ],
            [
                'nombre' => 'Paquete Adulto Mayor Integral',
                'numero_sesiones' => 12,
                'precio' => 3840.00, // 20% descuento
                'tipo_terapia' => 'Terapia Geriátrica',
                'especifico_enfermedad' => 'Mejora integral de calidad de vida en adultos mayores'
            ],

            // Paquetes Pediátricos
            [
                'nombre' => 'Paquete Pediátrico Básico',
                'numero_sesiones' => 6,
                'precio' => 2550.00, // 15% descuento
                'tipo_terapia' => 'Fisioterapia Pediátrica',
                'especifico_enfermedad' => 'Desarrollo motor, tortícolis, plagiocefalia'
            ],
            [
                'nombre' => 'Paquete Pediátrico Especializado',
                'numero_sesiones' => 12,
                'precio' => 4800.00, // 20% descuento
                'tipo_terapia' => 'Fisioterapia Pediátrica',
                'especifico_enfermedad' => 'Trastornos del desarrollo, parálisis cerebral, espina bífida'
            ],

            // Paquetes de Dolor Crónico
            [
                'nombre' => 'Paquete Manejo de Dolor',
                'numero_sesiones' => 8,
                'precio' => 4080.00, // 15% descuento
                'tipo_terapia' => 'Terapia de Dolor Crónico',
                'especifico_enfermedad' => 'Fibromialgia, artritis, dolor lumbar crónico'
            ],
            [
                'nombre' => 'Paquete Dolor Crónico Integral',
                'numero_sesiones' => 16,
                'precio' => 7680.00, // 20% descuento
                'tipo_terapia' => 'Terapia de Dolor Crónico',
                'especifico_enfermedad' => 'Manejo integral y multidisciplinario del dolor crónico'
            ],

            // Paquetes Respiratorios
            [
                'nombre' => 'Paquete Respiratorio Básico',
                'numero_sesiones' => 6,
                'precio' => 2652.00, // 15% descuento
                'tipo_terapia' => 'Fisioterapia Respiratoria',
                'especifico_enfermedad' => 'EPOC, asma, rehabilitación post-COVID'
            ],
            [
                'nombre' => 'Paquete Respiratorio Avanzado',
                'numero_sesiones' => 12,
                'precio' => 4992.00, // 20% descuento
                'tipo_terapia' => 'Fisioterapia Respiratoria',
                'especifico_enfermedad' => 'Enfermedades respiratorias crónicas, técnicas avanzadas'
            ],

            // Paquetes Post-Quirúrgicos
            [
                'nombre' => 'Paquete Post-Quirúrgico Básico',
                'numero_sesiones' => 8,
                'precio' => 4176.00, // 10% descuento
                'tipo_terapia' => 'Rehabilitación Post-Quirúrgica',
                'especifico_enfermedad' => 'Recuperación después de cirugías menores'
            ],
            [
                'nombre' => 'Paquete Post-Quirúrgico Completo',
                'numero_sesiones' => 16,
                'precio' => 7424.00, // 20% descuento
                'tipo_terapia' => 'Rehabilitación Post-Quirúrgica',
                'especifico_enfermedad' => 'Recuperación completa después de cirugías mayores'
            ],

            // Paquetes Combinados
            [
                'nombre' => 'Paquete Evaluación + Tratamiento',
                'numero_sesiones' => 5,
                'precio' => 2025.00, // 10% descuento
                'tipo_terapia' => 'Fisioterapia General',
                'especifico_enfermedad' => 'Evaluación completa + 4 sesiones de tratamiento'
            ],
            [
                'nombre' => 'Paquete Mantenimiento Mensual',
                'numero_sesiones' => 4,
                'precio' => 1530.00, // 15% descuento
                'tipo_terapia' => 'Fisioterapia General',
                'especifico_enfermedad' => 'Mantenimiento y prevención, 1 sesión por semana'
            ]
        ];

        foreach ($paquetes as $paquete) {
            PaqueteSesion::create($paquete);
        }
    }
}