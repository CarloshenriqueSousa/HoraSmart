<?php

/**
 * Model: WorkLog — Registro de jornada de trabalho diária.
 *
 * Cada funcionário tem no máximo UM WorkLog por dia (constraint unique no banco).
 * A jornada segue uma sequência obrigatória de 4 batidas:
 *
 *   clock_in → lunch_out → lunch_in → clock_out
 *   (Entrada)  (Almoço)    (Retorno)  (Saída)
 *
 * O campo 'status' controla a máquina de estados:
 *   in_progress → on_lunch → back_from_lunch → complete
 *
 * Ao completar a jornada, 'minutes_worked' é calculado automaticamente:
 *   minutes_worked = (lunch_out - clock_in) + (clock_out - lunch_in)
 *
 * Armazena em minutos (inteiro) em vez de decimal para evitar imprecisão float.
 *
 * Cálculos adicionais:
 *  - Horas extras: minutos acima de DAILY_WORKLOAD (480 min = 8h)
 *  - Período manhã: lunch_out - clock_in
 *  - Período tarde: clock_out - lunch_in
 *
 * Relacionamentos:
 *  - belongsTo Employee        → Funcionário dono do registro
 *  - hasMany   ClockAdjustment → Solicitações de ajuste de horário
 *
 * Accessors:
 *  - formatted_hours    → Converte minutes_worked para "HH:MM"
 *  - next_action        → Indica qual campo deve ser preenchido a seguir
 *  - overtime_minutes   → Minutos acima de 8h (0 se não completou)
 *  - formatted_overtime → Formato "+HH:MM" ou "—"
 *  - morning_minutes    → Minutos trabalhados no período da manhã
 *  - afternoon_minutes  → Minutos trabalhados no período da tarde
 *
 * Tecnologias: Laravel Eloquent, Carbon (casts datetime), Accessor pattern
 *
 * @see \App\Services\WorkLogService (lógica de punch)
 * @see \App\Http\Controllers\WorkLogController
 * @see \App\Models\ClockAdjustment
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkLog extends Model
{
    use HasFactory;

    /** Carga horária diária padrão em minutos (8h) */
    const DAILY_WORKLOAD = 480;

    /** Carga horária mensal padrão em minutos (22 dias úteis × 8h) */
    const MONTHLY_WORKLOAD = 10560;

    protected $fillable = [
        'employee_id',
        'work_date',
        'clock_in',
        'lunch_out',
        'lunch_in',
        'clock_out',
        'minutes_worked',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'work_date' => 'date',
            'clock_in'  => 'datetime',
            'lunch_out' => 'datetime',
            'lunch_in'  => 'datetime',
            'clock_out' => 'datetime',
        ];
    }

    public function employee(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function adjustments(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ClockAdjustment::class);
    }

    /**
     * Horas trabalhadas formatadas como "HH:MM".
     */
    public function getFormattedHoursAttribute(): string
    {
        if (is_null($this->minutes_worked)) {
            return '--:--';
        }

        $hours   = intdiv($this->minutes_worked, 60);
        $minutes = $this->minutes_worked % 60;

        return sprintf('%02d:%02d', $hours, $minutes);
    }

    /**
     * Próxima ação na sequência de batidas (qual campo preencher).
     *
     * Caso especial: log recém-criado tem status 'in_progress' mas clock_in ainda é null,
     * pois o firstOrCreate não popula clock_in automaticamente. Nesse caso a primeira
     * ação é registrar o clock_in.
     */
    public function getNextActionAttribute(): ?string
    {
        // Primeira batida do dia: log criado mas clock_in ainda não foi registrado
        if ($this->status === 'in_progress' && is_null($this->clock_in)) {
            return 'clock_in';
        }

        return match ($this->status) {
            'in_progress'     => 'lunch_out',
            'on_lunch'        => 'lunch_in',
            'back_from_lunch' => 'clock_out',
            default           => null,
        };
    }

    /**
     * Minutos de hora extra (acima de 8h). Retorna 0 se jornada não completa.
     */
    public function getOvertimeMinutesAttribute(): int
    {
        if (is_null($this->minutes_worked)) {
            return 0;
        }

        $workload = $this->employee->daily_workload ?? self::DAILY_WORKLOAD;
        $tolerance = $this->employee->overtime_tolerance ?? 10;
        
        $diff = $this->minutes_worked - $workload;

        return ($diff > $tolerance) ? $diff : 0;
    }

    /**
     * Horas extras formatadas como "+HH:MM" ou "—".
     */
    public function getFormattedOvertimeAttribute(): string
    {
        $overtime = $this->overtime_minutes;

        if ($overtime <= 0) {
            return '—';
        }

        $hours   = intdiv($overtime, 60);
        $minutes = $overtime % 60;

        return sprintf('+%02d:%02d', $hours, $minutes);
    }

    /**
     * Minutos trabalhados no período da manhã (clock_in → lunch_out).
     */
    public function getMorningMinutesAttribute(): int
    {
        if (!$this->clock_in || !$this->lunch_out) {
            return 0;
        }

        return (int) $this->lunch_out->diffInMinutes($this->clock_in);
    }

    /**
     * Minutos trabalhados no período da tarde (lunch_in → clock_out).
     */
    public function getAfternoonMinutesAttribute(): int
    {
        if (!$this->lunch_in || !$this->clock_out) {
            return 0;
        }

        return (int) $this->clock_out->diffInMinutes($this->lunch_in);
    }

    /**
     * Duração do almoço em minutos.
     */
    public function getLunchMinutesAttribute(): int
    {
        if (!$this->lunch_out || !$this->lunch_in) {
            return 0;
        }

        return (int) $this->lunch_in->diffInMinutes($this->lunch_out);
    }

    public function isComplete(): bool
    {
        return $this->status === 'complete';
    }
}