<?php

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
                $field = $adjustment->field;
                $adjustment->workLog->update([
                    $field => $adjustment->requested_time,
                ]);
            }
        });

        $message = $request->status === 'approved'
            ? 'Ajuste aprovado com sucesso.'
            : 'Ajuste rejeitado.';

        return redirect()->route('adjustments.index')
            ->with('success', $message);
    }
}