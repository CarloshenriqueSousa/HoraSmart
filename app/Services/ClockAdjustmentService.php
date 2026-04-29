<?php

/**
 * Service: ClockAdjustmentService — Lógica de negócio de ajustes de ponto.
 *
 * Centraliza a lógica de aprovação/rejeição de ajustes de ponto, incluindo
 * a atualização do WorkLog quando aprovado e o recálculo das horas.
 *
 * @see \App\Http\Controllers\ClockAdjustmentController
 * @see \App\Models\ClockAdjustment
 */

namespace App\Services;

use App\Enums\AdjustmentStatus;
use App\Models\ClockAdjustment;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class ClockAdjustmentService
{
    /**
     * Processa a revisão de um ajuste de ponto por um gestor.
     */
    public function review(ClockAdjustment $adjustment, AdjustmentStatus $status, User $reviewer, ?string $comment = null): void
    {
        DB::transaction(function () use ($adjustment, $status, $reviewer, $comment) {
            $adjustment->update([
                'status'           => $status,
                'reviewed_by'      => $reviewer->id,
                'reviewer_comment' => $comment,
                'reviewed_at'      => now(),
            ]);

            if ($status === AdjustmentStatus::Approved) {
                $field   = $adjustment->field;
                $workLog = $adjustment->workLog;

                $workLog->update([$field => $adjustment->requested_time]);
                $workLog->refresh();
                $workLog->recalculateMinutes();
            }
        });
    }
}
