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

        // Generar horarios cada hora desde las 8:00 hasta las 17:00
        // Esto da 10 horarios posibles: 08:00, 09:00, 10:00, 11:00, 12:00, 13:00, 14:00, 15:00, 16:00, 17:00
        for ($hora = $horaInicio; $hora < $horaFin; $hora++) {
            $fechaHora = $fecha->copy()->hour($hora)->minute(0)->second(0);

            // Verificar que la cita no se extienda más allá del horario laboral
            if ($fechaHora->copy()->addMinutes($duracion)->hour > $horaFin) {
                continue;
            }

            // Para fechas futuras, permitir todas las horas
            // Para el día de hoy, solo permitir horas que no hayan pasado
            if ($fecha->isToday()) {
                if ($fechaHora->isPast()) {
                    continue;
                }
            } else if ($fecha->isPast()) {
                // Si la fecha completa está en el pasado, no devolver ninguna hora
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
     * Método de debug para ver por qué solo aparecen ciertas horas
     */
    public static function debugHorasDisponibles($fecha, $terapeutaId, $duracion = 60)
    {
        $fecha = Carbon::parse($fecha);
        $horaInicio = 8;
        $horaFin = 18;
        $debug = [];

        for ($hora = $horaInicio; $hora < $horaFin; $hora++) {
            $fechaHora = $fecha->copy()->hour($hora)->minute(0)->second(0);
            
            $info = [
                'hora' => $fechaHora->format('H:i'),
                'fecha_hora_completa' => $fechaHora->format('Y-m-d H:i:s'),
                'es_pasado' => $fechaHora->isPast(),
                'es_hoy' => $fecha->isToday(),
                'hora_actual' => Carbon::now()->format('H:i:s'),
            ];

            // Verificar si se extiende más allá del horario
            if ($fechaHora->copy()->addMinutes($duracion)->hour > $horaFin) {
                $info['razon'] = 'Se extiende más allá del horario laboral';
                $debug[] = $info;
                continue;
            }

            // Verificar si es en el pasado
            if ($fecha->isToday()) {
                if ($fechaHora->isPast()) {
                    $info['razon'] = 'Hora ya pasada (es hoy)';
                    $debug[] = $info;
                    continue;
                }
            } else if ($fecha->isPast()) {
                $info['razon'] = 'Fecha completa en el pasado';
                $debug[] = $info;
                continue;
            }

            // Verificar disponibilidad
            $estaDisponible = static::estaDisponible($fechaHora, $terapeutaId, $duracion);
            $info['esta_disponible'] = $estaDisponible;
            
            if ($estaDisponible) {
                $info['razon'] = 'DISPONIBLE';
            } else {
                $info['razon'] = 'Ocupada o en conflicto';
            }

            $debug[] = $info;
        }

        return $debug;
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
