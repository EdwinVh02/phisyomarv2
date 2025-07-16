<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Usuario>
 */
class UsuarioFactory extends Factory
{
    public function definition(): array
    {
        return [
            'nombre' => $this->faker->firstName,
            'apellido_paterno' => $this->faker->lastName,
            'apellido_materno' => $this->faker->lastName,
            'correo_electronico' => $this->faker->unique()->safeEmail,
            'contraseña' => 'Password123!', // El mutador del modelo la hashea
            'telefono' => $this->faker->phoneNumber,
            'fecha_nacimiento' => $this->faker->date(),
            'sexo' => $this->faker->randomElement(['Masculino', 'Femenino']),
            'curp' => strtoupper($this->faker->bothify('????######???????')),
            'estatus' => 'activo',
            'rol_id' => 4, // Por default paciente
        ];
    }

    public function administrador()
    {
        return $this->state([
            'rol_id' => 1,
            'correo_electronico' => 'admin@phisyomar.com',
        ]);
    }

    public function terapeuta()
    {
        return $this->state([
            'rol_id' => 2,
        ]);
    }

    public function recepcionista()
    {
        return $this->state([
            'rol_id' => 3,
        ]);
    }

    public function paciente()
    {
        return $this->state([
            'rol_id' => 4,
        ]);
    }
}
