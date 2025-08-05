<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Recepcionista extends Model
{
    use HasFactory;

    protected $table = 'recepcionistas';

    protected $primaryKey = 'id';

    public $timestamps = false;

    protected $fillable = ['id'];

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'id');
    }
}
