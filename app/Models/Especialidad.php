<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Especialidad extends Model
{
    protected $table = 'especialidades';
    
    public $timestamps = false;

    protected $fillable = ['nombre'];

    public function terapeutas(): BelongsToMany
    {
        return $this->belongsToMany(
            Terapeuta::class,
            'terapeuta_especialidad',
            'especialidad_id',
            'terapeuta_id'
        );
    }
}
