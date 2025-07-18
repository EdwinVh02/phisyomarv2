<?php

namespace App\Rules;

use App\Models\Cita;
use App\Models\Terapeuta;
use Carbon\Carbon;
use Illuminate\Contracts\Validation\Rule;

class CitaDisponible implements Rule
{
    private $terapeutaId;

    private $duracion;

    private $citaId;

    private $mensaje;

    public function __construct($terapeutaId, $duracion = 60, $citaId = null)
    {
        $this->terapeutaId = $terapeutaId;
        $this->duracion = $duracion;
        $this->citaId = $citaId;
    }

    public function passes($attribute, $value)
    {
        try {
            $fechaHora = Carbon::parse($value);
        } catch (\Exception $e) {
            $this->mensaje = 'La fecha y hora proporcionada no es válida.';

            return false;
        }

        // Validar que la fecha no sea en el pasado
        if ($fechaHora->isPast()) {
            $this->mensaje = 'No se pueden agendar citas en fechas pasadas.';

            return false;
        }

        // Validar que la fecha esté dentro de horario laboral (ejemplo: 8:00 AM - 6:00 PM)
        $hora = $fechaHora->hour;
        if ($hora < 8 || $hora >= 18) {
            $this->mensaje = 'Las citas solo se pueden agendar entre las 8:00 AM y 6:00 PM.';

            return false;
        }

        // Validar que no sea domingo
        if ($fechaHora->dayOfWeek === Carbon::SUNDAY) {
            $this->mensaje = 'No se pueden agendar citas los domingos.';

            return false;
        }

        // Validar que el terapeuta exista
        if (! Terapeuta::find($this->terapeutaId)) {
            $this->mensaje = 'El terapeuta seleccionado no existe.';

            return false;
        }

        // Validar disponibilidad del terapeuta
        $fechaInicio = $fechaHora;
        $fechaFin = $fechaHora->copy()->addMinutes($this->duracion);

        $citasConflicto = Cita::where('terapeuta_id', $this->terapeutaId)
            ->where('estado', '!=', 'cancelada')
            ->where(function ($query) use ($fechaInicio, $fechaFin) {
                $query->where(function ($q) use ($fechaInicio) {
                    // Cita existente que inicia antes y termina después del inicio de la nueva cita
                    $q->where('fecha_hora', '<=', $fechaInicio)
                        ->whereRaw('DATE_ADD(fecha_hora, INTERVAL COALESCE(duracion, 60) MINUTE) > ?', [$fechaInicio]);
                })->orWhere(function ($q) use ($fechaInicio, $fechaFin) {
                    // Cita existente que inicia durante la nueva cita
                    $q->where('fecha_hora', '>=', $fechaInicio)
                        ->where('fecha_hora', '<', $fechaFin);
                });
            });

        // Excluir la cita actual si estamos actualizando
        if ($this->citaId) {
            $citasConflicto->where('id', '!=', $this->citaId);
        }

        if ($citasConflicto->exists()) {
            $citaExistente = $citasConflicto->first();
            $horaExistente = $citaExistente->fecha_hora->format('H:i');

            // Obtener horas disponibles para ese día
            $fecha = $fechaHora->format('Y-m-d');
            $horasDisponibles = Cita::horasDisponibles($fecha, $this->terapeutaId, $this->duracion);

            if (empty($horasDisponibles)) {
                $this->mensaje = "El terapeuta ya tiene una cita a las {$horaExistente} y no hay más horarios disponibles para el ".$fechaHora->format('d/m/Y').'. Por favor, selecciona otra fecha.';
            } else {
                $horasTexto = implode(', ', array_slice($horasDisponibles, 0, 5));
                if (count($horasDisponibles) > 5) {
                    $horasTexto .= ' y '.(count($horasDisponibles) - 5).' más';
                }
                $this->mensaje = "El terapeuta ya tiene una cita a las {$horaExistente}. Horas disponibles para el ".$fechaHora->format('d/m/Y').": {$horasTexto}";
            }

            return false;
        }

        // Validar que no se excedan las citas por día del terapeuta (máximo 8 citas por día)
        $citasDelDia = Cita::where('terapeuta_id', $this->terapeutaId)
            ->whereDate('fecha_hora', $fechaHora->toDateString())
            ->where('estado', '!=', 'cancelada');

        if ($this->citaId) {
            $citasDelDia->where('id', '!=', $this->citaId);
        }

        if ($citasDelDia->count() >= 8) {
            $this->mensaje = 'El terapeuta ya tiene el máximo de citas permitidas para este día.';

            return false;
        }

        return true;
    }

    public function message()
    {
        return $this->mensaje ?? 'La fecha y hora seleccionada no está disponible.';
    }
}
