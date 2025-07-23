<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Especialidad extends Model
{
    protected $table = 'especialidades';
    
    public $timestamps = false;

    protected $fillable = ['nombre'];

    public function terapeutas()
    {
        return $this->belongsToMany(Terapeuta::class, 'terapeuta_especialidades', 'especialidad_id', 'terapeuta_id');
    }
}
