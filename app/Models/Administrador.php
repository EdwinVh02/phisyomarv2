<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Administrador extends Model
{
    use HasFactory;

    protected $table = 'administradores';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = ['id', 'cedula_profesional', 'clinica_id'];

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'id');
    }

    public function clinica()
    {
        return $this->belongsTo(Clinica::class, 'clinica_id');
    }
}
