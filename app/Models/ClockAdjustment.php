<?php

/**
 * Model: ClockAdjustment — Solicitação de ajuste de ponto.
 *
 * Quando um funcionário esquece de bater o ponto ou registra no horário errado,
 * pode solicitar uma correção ao gestor de RH.
 *
 * Fluxo de aprovação (via AdjustmentStatus enum):
 *   Pending → Approved | Rejected
 *
 * Relacionamentos:
 *  - belongsTo WorkLog → Registro de ponto que será ajustado
 *  - belongsTo User (requester) → Quem solicitou o ajuste
 *  - belongsTo User (reviewer)  → Quem revisou (null se pendente)
 *
 * @see \App\Enums\AdjustmentStatus
 * @see \App\Http\Controllers\ClockAdjustmentController
 */

namespace App\Models;

use App\Enums\AdjustmentStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClockAdjustment extends Model
{
    use HasFactory;

    protected $fillable = [
        'work_log_id',
        'requested_by',
        'reviewed_by',
        'field',
        'requested_time',
        'reason',
        'status',
        'reviewer_comment',
        'reviewed_at',
    ];

    protected function casts(): array
    {
        return [
            'requested_time' => 'datetime',
            'reviewed_at'    => 'datetime',
            'status'         => AdjustmentStatus::class,
        ];
    }

    public function workLog(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(WorkLog::class);
    }

    public function requester(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function reviewer(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function isPending(): bool
    {
        return $this->status === AdjustmentStatus::Pending;
    }
}