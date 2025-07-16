<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Paciente>
 */
class PacienteFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'contacto_emergencia_nombre' => $this->faker->name(),
            'contacto_emergencia_telefono' => '55' . $this->faker->numerify('########'),
            'contacto_emergencia_parentesco' => $this->faker->randomElement([
                'Esposo(a)', 'Hijo(a)', 'Padre', 'Madre', 'Hermano(a)', 'Primo(a)', 'Amigo(a)'
            ]),
            'tutor_nombre' => $this->faker->optional(0.3)->name(), // 30% chance de tener tutor
            'tutor_telefono' => $this->faker->optional(0.3)->numerify('55########'),
            'tutor_parentesco' => $this->faker->optional(0.3)->randomElement([
                'Padre', 'Madre', 'Tutor Legal', 'Abuelo(a)'
            ]),
            'tutor_direccion' => $this->faker->optional(0.3)->address(),
            'historial_medico_id' => null, // Se asignará después de crear historial
        ];
    }
}