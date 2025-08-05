<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Especialidad;

class EspecialidadesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $especialidades = [
            'Fisioterapia General',
            'Fisioterapia Deportiva',
            'Fisioterapia Neurológica',
            'Fisioterapia Traumatológica',
            'Fisioterapia Geriátrica',
            'Fisioterapia Pediátrica',
            'Fisioterapia Respiratoria',
            'Fisioterapia Cardiovascular',
            'Fisioterapia de la Mujer',
            'Fisioterapia Manual',
            'Electroterapia',
            'Hidroterapia',
            'Fisioterapia Oncológica',
            'Fisioterapia del Suelo Pélvico',
            'Osteopatía',
            'Quiropraxia',
            'Terapia Ocupacional',
            'Rehabilitación Postquirúrgica',
            'Fisioterapia Estética',
            'Fisioterapia Preventiva'
        ];

        foreach ($especialidades as $nombre) {
            Especialidad::create([
                'nombre' => $nombre
            ]);
        }
    }
}
