<?php

/**
 * Service: WorkLogService — Lógica de negócio do registro de ponto.
 *
 * Centraliza toda a lógica de "bater ponto" fora do controller, seguindo
 * o princípio de Single Responsibility. O controller apenas delega para cá.
 *
 * Responsabilidades:
 *  1. punch()      → Registra a próxima batida na sequência obrigatória
 *  2. Máquina de estados: in_progress → on_lunch → back_from_lunch → complete
 *  3. Cálculo automático de horas ao completar a jornada
 *  4. Labels localizados para status e botões da UI
 *
 * Regras de negócio implementadas:
 *  - Um registro por funcionário por dia (firstOrCreate com employee_id + work_date)
 *  - Sequência obrigatória de batidas (status → next_action)
 *  - Cálculo: (lunch_out - clock_in) + (clock_out - lunch_in)
 *  - Tudo dentro de DB::transaction para consistência
 *
 * Tecnologias: Laravel Service Pattern, Carbon, DB Transaction, Eloquent
 *
 * @see \App\Http\Controllers\WorkLogController::punch()
 * @see \App\Models\WorkLog
 */

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

        // DB::transaction garante atomicidade — evita race condition com cliques simultâneos
        $log = DB::transaction(function () use ($employee, $today) {
            return WorkLog::firstOrCreate(
                ['employee_id' => $employee->id, 'work_date' => $today],
                ['status' => 'in_progress']
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
            'clock_in'  => 'in_progress',   // Entrada registrada: continua in_progress
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
        // Log recém-criado: in_progress mas clock_in ainda não batido
        if ($log->status === 'in_progress' && is_null($log->clock_in)) {
            return 'Registrar Entrada';
        }

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