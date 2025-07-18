<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class Atiende extends Pivot
{
    protected $table = 'atiendes';

    protected $fillable = [
        'terapeuta_id',
        'cita_id',
    ];

    public function terapeuta()
    {
        return $this->belongsTo(Terapeuta::class, 'terapeuta_id');
    }

    public function cita()
    {
        return $this->belongsTo(Cita::class, 'cita_id');
    }
}
