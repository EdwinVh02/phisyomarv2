<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Terapeuta extends Model
{
    use HasFactory;

    protected $table = 'terapeutas';

    protected $primaryKey = 'id';

    public $timestamps = true;

    protected $fillable = [
        'id',
        'cedula_profesional',
        'especialidad_principal',
        'experiencia_anios',
        'estatus',
    ];

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'id');
    }

    public function especialidades(): BelongsToMany
    {
        return $this->belongsToMany(
            Especialidad::class,
            'terapeuta_especialidad',
            'terapeuta_id',
            'especialidad_id'
        );
    }

    public function experiencias()
    {
        return $this->hasMany(ExperienciaTerapeuta::class, 'terapeuta_id');
    }

    public function valoraciones()
    {
        return $this->hasMany(Valoracion::class, 'terapeuta_id');
    }
}
