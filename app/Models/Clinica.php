<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Clinica extends Model
{
    protected $table = 'clinicas';

    protected $fillable = [
        'Nombre',
        'Direccion',
        'Razon_Social',
        'RFC',
        'No_Licencia_Sanitaria',
        'No_Registro_Patronal',
        'No_Aviso_de_Funcionamiento',
        'Colores_Corporativos',
        'Logo_URL'
    ];

    public function administradores()
    {
        return $this->hasMany(Administrador::class, 'clinica_id');
    }
}
