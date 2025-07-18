<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Recepcionista extends Model
{
    use HasFactory;

    protected $table = 'recepcionistas';

    protected $primaryKey = 'Id';

    public $timestamps = false;

    protected $fillable = ['Id'];

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'Id');
    }
}
