<?php

/**
 * Controller: ClockAdjustmentController — Solicitações de ajuste de ponto.
 *
 * Funcionalidades:
 *  - index():  Lista ajustes — gestor vê todos, funcionário vê apenas os seus
 *  - create(): Formulário de solicitação (pré-carrega dados do WorkLog)
 *  - store():  Cria solicitação com status 'pending'
 *  - review(): Gestor aprova ou rejeita — se aprovado, atualiza o WorkLog automaticamente
 *
 * Fluxo de aprovação (review):
 *  1. Gestor escolhe 'approved' ou 'rejected'
 *  2. Se aprovado, o campo indicado (clock_in, lunch_out, etc.) é atualizado no WorkLog
 *  3. Tudo dentro de DB::transaction para consistência
 *
 * Segurança:
 *  - Funcionário só pode criar ajuste para seus próprios registros (authorize + policy)
 *  - Apenas gestor pode revisar (middleware 'role:gestor' na rota)
 *
 * Tecnologias: Laravel Controller, DB Transaction, Policies, Form Requests, Alpine.js (modal)
 *
 * @see \App\Http\Requests\StoreClockAdjustmentRequest
 * @see \App\Http\Requests\ReviewClockAdjustmentRequest
 * @see \App\Policies\ClockAdjustmentPolicy
 * @see resources/views/adjustments/index.blade.php (modal de revisão com Alpine.js)
 */

namespace App\Http\Controllers;

use App\Http\Requests\ReviewClockAdjustmentRequest;
use App\Http\Requests\StoreClockAdjustmentRequest;
use App\Models\ClockAdjustment;
use App\Models\WorkLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class ClockAdjustmentController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request)
    {
        $user = $request->user();

        if ($user->isGestor()) {
            $adjustments = ClockAdjustment::with('workLog.employee.user', 'requester')
                ->orderByDesc('created_at')
                ->paginate(20);
        } else {
            $adjustments = ClockAdjustment::with('workLog.employee.user', 'requester')
                ->where('requested_by', $user->id)
                ->orderByDesc('created_at')
                ->paginate(20);
        }

        return view('adjustments.index', compact('adjustments'));
    }

    public function create(Request $request)
    {
        $workLogId = $request->query('work_log_id');
        $workLog   = WorkLog::findOrFail($workLogId);

        $this->authorize('view', $workLog);

        return view('adjustments.create', compact('workLog'));
    }

    public function store(StoreClockAdjustmentRequest $request)
    {
        ClockAdjustment::create([
            'work_log_id'    => $request->work_log_id,
            'requested_by'   => $request->user()->id,
            'field'          => $request->field,
            'requested_time' => $request->requested_time,
            'reason'         => $request->reason,
            'status'         => 'pending',
        ]);

        return redirect()->route('adjustments.index')
            ->with('success', 'Solicitação de ajuste enviada com sucesso.');
    }

    public function review(ReviewClockAdjustmentRequest $request, ClockAdjustment $adjustment)
    {
        $this->authorize('review', ClockAdjustment::class);

        DB::transaction(function () use ($request, $adjustment) {
            $adjustment->update([
                'status'           => $request->status,
                'reviewed_by'      => $request->user()->id,
                'reviewer_comment' => $request->reviewer_comment,
                'reviewed_at'      => now(),
            ]);

            if ($request->status === 'approved') {
                $field   = $adjustment->field;
                $workLog = $adjustment->workLog;

                $workLog->update([$field => $adjustment->requested_time]);

                // Recarregar para pegar os valores atualizados
                $workLog->refresh();

                // Recalcular horas se a jornada estiver completa
                if ($workLog->clock_in && $workLog->lunch_out && $workLog->lunch_in && $workLog->clock_out) {
                    $morning   = $workLog->lunch_out->diffInMinutes($workLog->clock_in);
                    $afternoon = $workLog->clock_out->diffInMinutes($workLog->lunch_in);
                    $workLog->update([
                        'minutes_worked' => max(0, $morning + $afternoon),
                        'status'         => 'complete',
                    ]);
                }
            }
        });

        $message = $request->status === 'approved'
            ? 'Ajuste aprovado com sucesso.'
            : 'Ajuste rejeitado.';

        return redirect()->route('adjustments.index')
            ->with('success', $message);
    }
}