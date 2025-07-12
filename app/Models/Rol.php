<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Rol extends Model
{
    protected $table = 'rol';
    public $timestamps = false;

    protected $fillable = ['Nombre'];

    public function usuarios()
    {
        return $this->hasMany(Usuario::class, 'RolId');
    }
}
