<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Rol extends Model
{
    protected $table = 'rols';
    public $timestamps = false;

    protected $fillable = ['name'];

    public function usuarios()
    {
        return $this->hasMany(Usuario::class, 'rol_id');
    }
}
