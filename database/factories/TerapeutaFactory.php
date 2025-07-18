<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Terapeuta>
 */
class TerapeutaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'cedula_profesional' => $this->faker->unique()->numerify('########'),
            'especialidad_principal' => $this->faker->randomElement([
                'Fisioterapia Deportiva',
                'Fisioterapia Neurológica',
                'Fisioterapia Ortopédica',
                'Fisioterapia Respiratoria',
                'Fisioterapia Pediátrica',
                'Fisioterapia Geriátrica',
                'Rehabilitación Cardíaca',
            ]),
            'experiencia_anios' => $this->faker->numberBetween(1, 25),
            'estatus' => 'activo',
        ];
    }
}
