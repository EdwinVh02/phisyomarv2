<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Recepcionista extends Model
{
    protected $table = 'recepcionista';
    protected $primaryKey = 'Id';
    public $timestamps = false;

    protected $fillable = ['Id'];

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'Id');
    }
}
