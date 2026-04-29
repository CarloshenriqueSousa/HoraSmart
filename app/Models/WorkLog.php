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
 * O campo 'status' controla a máquina de estados via WorkLogStatus enum:
 *   InProgress → OnLunch → BackFromLunch → Complete
 *
 * Ao completar a jornada, 'minutes_worked' é calculado automaticamente:
 *   minutes_worked = (lunch_out - clock_in) + (clock_out - lunch_in)
 *
 * Armazena em minutos (inteiro) em vez de decimal para evitar imprecisão float.
 *
 * Usa SoftDeletes para manter histórico auditável — dados de ponto são documentos legais.
 *
 * Relacionamentos:
 *  - belongsTo Employee        → Funcionário dono do registro
 *  - hasMany   ClockAdjustment → Solicitações de ajuste de horário
 *
 * @see \App\Enums\WorkLogStatus
 * @see \App\Services\WorkLogService
 * @see \App\Http\Controllers\WorkLogController
 */

namespace App\Models;

use App\Enums\WorkLogStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class WorkLog extends Model
{
    use HasFactory, SoftDeletes;

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
            'status'    => WorkLogStatus::class,
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

    // ─── Cálculo centralizado (DRY) ─────────────────────────────

    /**
     * Calcula os minutos trabalhados com base nas 4 batidas.
     * Método estático para que possa ser chamado de qualquer lugar.
     */
    public static function calculateWorkedMinutes(self $log): int
    {
        if (!$log->clock_in || !$log->lunch_out || !$log->lunch_in || !$log->clock_out) {
            return 0;
        }

        $morning   = abs($log->lunch_out->diffInMinutes($log->clock_in, false));
        $afternoon = abs($log->clock_out->diffInMinutes($log->lunch_in, false));

        return max(0, (int) ($morning + $afternoon));
    }

    /**
     * Recalcula e persiste os minutos trabalhados. Usado quando batidas são editadas.
     */
    public function recalculateMinutes(): void
    {
        if ($this->clock_in && $this->lunch_out && $this->lunch_in && $this->clock_out) {
            $this->update([
                'minutes_worked' => self::calculateWorkedMinutes($this),
                'status'         => WorkLogStatus::Complete,
            ]);
        }
    }

    // ─── Accessors ──────────────────────────────────────────────

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
     */
    public function getNextActionAttribute(): ?string
    {
        // Primeira batida do dia: log criado mas clock_in ainda não foi registrado
        if ($this->status === WorkLogStatus::InProgress && is_null($this->clock_in)) {
            return 'clock_in';
        }

        return match ($this->status) {
            WorkLogStatus::InProgress    => 'lunch_out',
            WorkLogStatus::OnLunch       => 'lunch_in',
            WorkLogStatus::BackFromLunch => 'clock_out',
            default                      => null,
        };
    }

    /**
     * Minutos de hora extra (acima da carga horária). Retorna 0 se jornada não completa.
     */
    public function getOvertimeMinutesAttribute(): int
    {
        if (is_null($this->minutes_worked)) {
            return 0;
        }

        $workload  = $this->employee->daily_workload ?? 480;
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
        return $this->status === WorkLogStatus::Complete;
    }
}