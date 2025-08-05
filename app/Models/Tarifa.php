<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tarifa extends Model
{
    protected $table = 'tarifas';

    public $timestamps = false;

    protected $fillable = [
        'titulo',
        'precio',
        'tipo',
        'condiciones',
    ];

    protected $casts = [
        'precio' => 'decimal:2',
    ];

    public function tratamientos()
    {
        return $this->hasMany(Tratamiento::class, 'tarifa_id');
    }
}
