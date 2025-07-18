<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Clinica>
 */
class ClinicaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $nombres = [
            'PhisyoMar - Clínica Principal',
            'Centro de Rehabilitación Integral',
            'Clínica de Fisioterapia Avanzada',
            'Instituto de Terapia Física',
            'Centro Médico de Rehabilitación',
        ];

        return [
            'nombre' => $this->faker->randomElement($nombres),
            'direccion' => $this->faker->address(),
            'telefono' => '55'.$this->faker->numerify('########'),
            'email' => $this->faker->companyEmail(),
            'horario_atencion' => 'Lunes a Viernes 8:00 - 20:00, Sábados 9:00 - 14:00',
        ];
    }
}
