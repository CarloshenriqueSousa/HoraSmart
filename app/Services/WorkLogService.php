<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\WorkLog;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class WorkLogService
{
    public function punch(Employee $employee): array
    {
        $today = today()->toDateString();

        $log = WorkLog::firstOrCreate(
            ['employee_id' => $employee->id, 'work_date' => $today],
            ['status' => 'in_progress']
        );

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

            if ($log->status === 'complete') {
                $log->minutes_worked = $this->calculateMinutes($log);
            }

            $log->save();
        });

        return [
            'success' => true,
            'message' => $this->actionLabel($action) . ' registrada com sucesso.',
            'log'     => $log->fresh(),
        ];
    }

    private function nextStatus(string $action): string
    {
        return match ($action) {
            'lunch_out' => 'on_lunch',
            'lunch_in'  => 'back_from_lunch',
            'clock_out' => 'complete',
            default     => 'in_progress',
        };
    }

    private function calculateMinutes(WorkLog $log): int
    {
        $morning   = $log->lunch_out->diffInMinutes($log->clock_in);
        $afternoon = $log->clock_out->diffInMinutes($log->lunch_in);

        return $morning + $afternoon;
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
        return match ($log->status) {
            'in_progress'     => 'Registrar Saída para Almoço',
            'on_lunch'        => 'Registrar Retorno do Almoço',
            'back_from_lunch' => 'Registrar Saída Final',
            'complete'        => 'Jornada Finalizada',
            default           => 'Registrar Entrada',
        };
    }

    public function statusLabel(string $status): string
    {
        return match ($status) {
            'in_progress'     => 'Trabalhando',
            'on_lunch'        => 'No almoço',
            'back_from_lunch' => 'Retornou do almoço',
            'complete'        => 'Jornada completa',
            default           => 'Não iniciado',
        };
    }
}