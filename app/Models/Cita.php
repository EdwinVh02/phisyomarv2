<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Cita extends Model
{
    protected $table = 'citas';

    protected $fillable = [
        'fecha_hora',
        'tipo',
        'duracion',
        'ubicacion',
        'equipo_asignado',
        'motivo',
        'estado',
        'paciente_id',
        'terapeuta_id',
        'registro_id',
        'paquete_paciente_id',
        'observaciones',
        'escala_dolor_eva_inicio',
        'escala_dolor_eva_fin',
        'como_fue_lesion',
        'antecedentes_patologicos',
        'antecedentes_no_patologicos',
    ];

    protected $casts = [
        'fecha_hora' => 'datetime',
        'duracion' => 'integer',
        'escala_dolor_eva_inicio' => 'integer',
        'escala_dolor_eva_fin' => 'integer',
    ];

    public function paciente()
    {
        return $this->belongsTo(Paciente::class, 'paciente_id');
    }

    public function terapeuta()
    {
        return $this->belongsTo(Terapeuta::class, 'terapeuta_id');
    }

    public function paquetePaciente()
    {
        return $this->belongsTo(PaquetePaciente::class, 'paquete_paciente_id');
    }

    public function registro()
    {
        return $this->belongsTo(Registro::class, 'registro_id');
    }

    public function pagos()
    {
        return $this->hasMany(Pago::class, 'cita_id');
    }

    public function pagoActual()
    {
        return $this->hasOne(Pago::class, 'cita_id')->latest();
    }

    /**
     * Verificar si una fecha y hora está disponible para un terapeuta
     */
    public static function estaDisponible($fechaHora, $terapeutaId, $duracion = 60, $citaId = null)
    {
        $fechaInicio = Carbon::parse($fechaHora);
        $fechaFin = $fechaInicio->copy()->addMinutes($duracion);

        $query = static::where('terapeuta_id', $terapeutaId)
            ->where('estado', '!=', 'cancelada')
            ->where(function ($query) use ($fechaInicio, $fechaFin) {
                $query->where(function ($q) use ($fechaInicio) {
                    $q->where('fecha_hora', '<=', $fechaInicio)
                        ->whereRaw('DATE_ADD(fecha_hora, INTERVAL COALESCE(duracion, 60) MINUTE) > ?', [$fechaInicio]);
                })->orWhere(function ($q) use ($fechaInicio, $fechaFin) {
                    $q->where('fecha_hora', '>=', $fechaInicio)
                        ->where('fecha_hora', '<', $fechaFin);
                });
            });

        if ($citaId) {
            $query->where('id', '!=', $citaId);
        }

        return ! $query->exists();
    }

    /**
     * Obtener las horas disponibles para un terapeuta en una fecha específica
     */
    public static function horasDisponibles($fecha, $terapeutaId, $duracion = 60)
    {
        $fecha = Carbon::parse($fecha);

        // Horario laboral: 8:00 AM - 6:00 PM
        $horaInicio = 8;
        $horaFin = 18;

        // Si es domingo, no hay horarios disponibles
        if ($fecha->dayOfWeek === Carbon::SUNDAY) {
            return [];
        }

        $horasDisponibles = [];

        // Para citas de 1 hora, generar horarios cada hora exacta
        for ($hora = $horaInicio; $hora < $horaFin; $hora++) {
            $fechaHora = $fecha->copy()->hour($hora)->minute(0)->second(0);

            // Verificar que la cita no se extienda más allá del horario laboral
            if ($fechaHora->copy()->addMinutes($duracion)->hour > $horaFin) {
                continue;
            }

            // Verificar que no sea en el pasado
            if ($fechaHora->isPast()) {
                continue;
            }

            // Verificar disponibilidad
            if (static::estaDisponible($fechaHora, $terapeutaId, $duracion)) {
                $horasDisponibles[] = $fechaHora->format('H:i');
            }
        }

        return $horasDisponibles;
    }

    /**
     * Obtener las fechas disponibles para un terapeuta en un rango de fechas
     */
    public static function fechasDisponibles($fechaInicio, $fechaFin, $terapeutaId, $duracion = 60)
    {
        $fechaInicio = Carbon::parse($fechaInicio);
        $fechaFin = Carbon::parse($fechaFin);
        $fechasDisponibles = [];

        $fecha = $fechaInicio->copy();

        while ($fecha->lte($fechaFin)) {
            // Saltar domingos
            if ($fecha->dayOfWeek !== Carbon::SUNDAY) {
                $horasDisponibles = static::horasDisponibles($fecha, $terapeutaId, $duracion);

                if (! empty($horasDisponibles)) {
                    $fechasDisponibles[] = [
                        'fecha' => $fecha->format('Y-m-d'),
                        'dia_semana' => $fecha->locale('es')->dayName,
                        'horas_disponibles' => $horasDisponibles,
                    ];
                }
            }

            $fecha->addDay();
        }

        return $fechasDisponibles;
    }

    /**
     * Obtener el próximo horario disponible para un terapeuta
     */
    public static function proximoHorarioDisponible($terapeutaId, $duracion = 60)
    {
        $fechaInicio = Carbon::now()->addDay(); // Comenzar desde mañana
        $fechaFin = $fechaInicio->copy()->addDays(30); // Buscar en los próximos 30 días

        $fecha = $fechaInicio->copy();

        while ($fecha->lte($fechaFin)) {
            if ($fecha->dayOfWeek !== Carbon::SUNDAY) {
                $horasDisponibles = static::horasDisponibles($fecha, $terapeutaId, $duracion);

                if (! empty($horasDisponibles)) {
                    return [
                        'fecha' => $fecha->format('Y-m-d'),
                        'hora' => $horasDisponibles[0],
                        'fecha_hora' => $fecha->format('Y-m-d').' '.$horasDisponibles[0].':00',
                    ];
                }
            }

            $fecha->addDay();
        }

        return null;
    }
}
