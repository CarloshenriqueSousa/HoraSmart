<?php

namespace App\Http\Controllers;

use App\Models\WorkLog;
use App\Services\WorkLogService;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class WorkLogController extends Controller
{
    Use AuthorizesRequests;
    public function __construct(protected WorkLogService $service) {}

    public function index(Request $request)
    {
        $user = $request->user();

        if ($user->isGestor()) {
            $logs = WorkLog::with('employee.user')
                ->orderByDesc('work_date')
                ->paginate(20);
        } else {
            $logs = $user->employee->workLogs()
                ->orderByDesc('work_date')
                ->paginate(20);
        }

        return view('worklogs.index', compact('logs'));
    }

    public function punch(Request $request)
    {
        $employee = $request->user()->employee;

        if (!$employee) {
            return response()->json([
                'success' => false,
                'message' => 'Perfil de funcionário não encontrado.',
            ], 403);
        }

        $result = $this->service->punch($employee);

        return response()->json($result);
    }

    public function show(WorkLog $workLog)
    {
        $this->authorize('view', $workLog);
        $workLog->load('employee.user', 'adjustments.requester');
        return view('worklogs.show', compact('workLog'));
    }
}