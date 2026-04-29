<?php

/**
 * Service: WorkLogService — Lógica de negócio do registro de ponto.
 *
 * Centraliza toda a lógica de "bater ponto" fora do controller, seguindo
 * o princípio de Single Responsibility.
 *
 * Responsabilidades:
 *  1. punch()      → Registra a próxima batida na sequência obrigatória
 *  2. Máquina de estados via WorkLogStatus enum
 *  3. Cálculo centralizado de horas via WorkLog::calculateWorkedMinutes()
 *  4. Labels localizados para status e botões da UI
 *
 * @see \App\Http\Controllers\WorkLogController::punch()
 * @see \App\Models\WorkLog
 * @see \App\Enums\WorkLogStatus
 */

namespace App\Services;

use App\Enums\WorkLogStatus;
use App\Models\Employee;
use App\Models\WorkLog;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class WorkLogService
{
    public function punch(Employee $employee): array
    {
        $today = today()->startOfDay();

        // DB::transaction garante atomicidade — evita race condition com cliques simultâneos
        $log = DB::transaction(function () use ($employee, $today) {
            return WorkLog::firstOrCreate(
                ['employee_id' => $employee->id, 'work_date' => $today],
                ['status' => WorkLogStatus::InProgress]
            );
        });

        if ($log->isComplete()) {
            return ['success' => false, 'message' => 'Jornada do dia já finalizada.'];
        }

        $action = $log->next_action;

        if (is_null($action)) {
            return ['success' => false, 'message' => 'Nenhuma ação disponível no momento.'];
        }

        DB::transaction(function () use ($log, $action) {
            $log->$action = Carbon::now();
            $log->status  = $this->nextStatus($action);

            if ($log->status === WorkLogStatus::Complete) {
                $log->minutes_worked = WorkLog::calculateWorkedMinutes($log);
            }

            $log->save();
        });

        return [
            'success' => true,
            'message' => $this->actionLabel($action) . ' registrada com sucesso.',
            'log'     => $log->fresh(),
        ];
    }

    private function nextStatus(string $action): WorkLogStatus
    {
        return match ($action) {
            'clock_in'  => WorkLogStatus::InProgress,
            'lunch_out' => WorkLogStatus::OnLunch,
            'lunch_in'  => WorkLogStatus::BackFromLunch,
            'clock_out' => WorkLogStatus::Complete,
            default     => WorkLogStatus::InProgress,
        };
    }

    private function actionLabel(string $action): string
    {
        return match ($action) {
            'clock_in'  => 'Entrada',
            'lunch_out' => 'Saída para almoço',
            'lunch_in'  => 'Retorno do almoço',
            'clock_out' => 'Saída final',
            default     => 'Registro',
        };
    }

    public function currentButtonLabel(WorkLog $log): string
    {
        // Log recém-criado: in_progress mas clock_in ainda não batido
        if ($log->status === WorkLogStatus::InProgress && is_null($log->clock_in)) {
            return 'Registrar Entrada';
        }

        return $log->status->buttonLabel();
    }

    public function statusLabel(WorkLogStatus|string $status): string
    {
        if ($status instanceof WorkLogStatus) {
            return $status->label();
        }

        $enum = WorkLogStatus::tryFrom($status);
        return $enum?->label() ?? 'Não iniciado';
    }
}