<?php

/**
 * Controller: WorkLogController — Registros de ponto (jornada de trabalho).
 *
 * Funcionalidades:
 *  - index(): Lista registros — gestor vê todos, funcionário vê apenas os seus
 *  - punch(): Registra batida via AJAX (POST /punch) — retorna JSON
 *  - show():  Detalhes de um registro
 *  - edit():  Formulário de edição (apenas gestor)
 *  - update(): Atualiza horários e recalcula minutos via WorkLog::recalculateMinutes()
 *  - export(): Exporta registros como CSV
 *  - exportPdf(): Exporta relatório mensal como PDF
 *
 * @see \App\Services\WorkLogService
 * @see \App\Enums\WorkLogStatus
 */

namespace App\Http\Controllers;

use App\Enums\WorkLogStatus;
use App\Models\WorkLog;
use App\Services\WorkLogService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Symfony\Component\HttpFoundation\StreamedResponse;

class WorkLogController extends Controller
{
    use AuthorizesRequests;

    public function __construct(protected WorkLogService $service) {}

    public function index(Request $request)
    {
        $user       = $request->user();
        $month      = $request->query('month');
        $employeeId = $request->query('employee_id');

        if ($user->isGestor()) {
            $query = WorkLog::with('employee.user');

            if ($employeeId) {
                $query->where('employee_id', $employeeId);
            }
        } else {
            $query = $user->employee->workLogs();
        }

        // Filtro por mês
        if ($month && preg_match('/^\d{4}-\d{2}$/', $month)) {
            [$year, $m] = explode('-', $month);
            $query->whereYear('work_date', $year)->whereMonth('work_date', $m);
        }

        $logs = $query->orderByDesc('work_date')->paginate(20)->withQueryString();

        $employees = $user->isGestor()
            ? \App\Models\Employee::with('user')->orderBy('created_at')->get()
            : collect();

        return view('workslogs.index', compact('logs', 'month', 'employees', 'employeeId'));
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
        return view('workslogs.show', compact('workLog'));
    }

    public function edit(WorkLog $workLog)
    {
        $this->authorize('view', $workLog);
        $workLog->load('employee.user');
        return view('workslogs.edit', compact('workLog'));
    }

    /**
     * Atualiza horários de um registro (apenas gestor).
     * Usa WorkLog::recalculateMinutes() para manter DRY.
     */
    public function update(Request $request, WorkLog $workLog)
    {
        $this->authorize('view', $workLog);

        $validated = $request->validate([
            'clock_in'  => ['nullable', 'date'],
            'lunch_out' => ['nullable', 'date', 'after:clock_in'],
            'lunch_in'  => ['nullable', 'date', 'after:lunch_out'],
            'clock_out' => ['nullable', 'date', 'after:lunch_in'],
        ]);

        $workLog->update($validated);
        $workLog->refresh();
        $workLog->recalculateMinutes();

        return redirect()->route('worklogs.show', $workLog)
            ->with('success', 'Registro atualizado com sucesso.');
    }

    /**
     * Exporta registros como CSV.
     */
    public function export(Request $request): StreamedResponse
    {
        $month = $request->query('month');

        $query = WorkLog::with('employee.user')
            ->where('status', WorkLogStatus::Complete)
            ->orderBy('work_date');

        if ($month && preg_match('/^\d{4}-\d{2}$/', $month)) {
            [$year, $m] = explode('-', $month);
            $query->whereYear('work_date', $year)->whereMonth('work_date', $m);
        }

        $logs = $query->get()->sortBy([
            ['employee.user.name', 'asc'],
            ['work_date', 'asc']
        ]);

        return response()->streamDownload(function () use ($logs) {
            $handle = fopen('php://output', 'w');

            // BOM para UTF-8 no Excel
            fwrite($handle, "\xEF\xBB\xBF");

            fputcsv($handle, [
                'Funcionário', 'Data', 'Entrada', 'Saída Almoço',
                'Retorno', 'Saída Final', 'Total (HH:MM)', 'Extras (HH:MM)',
            ], ';');

            foreach ($logs as $log) {
                fputcsv($handle, [
                    $log->employee->user->name,
                    $log->work_date->format('d/m/Y'),
                    $log->clock_in?->format('H:i')  ?? '',
                    $log->lunch_out?->format('H:i') ?? '',
                    $log->lunch_in?->format('H:i')  ?? '',
                    $log->clock_out?->format('H:i') ?? '',
                    $log->formatted_hours,
                    $log->formatted_overtime,
                ], ';');
            }

            fclose($handle);
        }, 'registros-ponto-' . ($month ?? date('Y-m')) . '.csv', [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    /**
     * Exporta relatório mensal como PDF.
     */
    public function exportPdf(Request $request)
    {
        $month = $request->query('month', date('Y-m'));

        if (!preg_match('/^\d{4}-\d{2}$/', $month)) {
            $month = date('Y-m');
        }

        [$year, $m] = explode('-', $month);

        $logs = WorkLog::with('employee.user')
            ->whereYear('work_date', $year)
            ->whereMonth('work_date', $m)
            ->where('status', WorkLogStatus::Complete)
            ->get()
            ->sortBy([
                ['employee.user.name', 'asc'],
                ['work_date', 'asc']
            ]);

        $byEmployee = $logs->groupBy('employee_id')->map(function ($employeeLogs) {
            return [
                'employee'       => $employeeLogs->first()->employee,
                'logs'           => $employeeLogs,
                'totalMinutes'   => $employeeLogs->sum('minutes_worked'),
                'overtimeTotal'  => $employeeLogs->sum(fn($l) => $l->overtime_minutes),
                'daysWorked'     => $employeeLogs->count(),
            ];
        });

        $monthName = \Carbon\Carbon::create($year, $m, 1)->translatedFormat('F Y');

        $pdf = Pdf::loadView('workslogs.pdf', compact('byEmployee', 'monthName', 'month'));
        $pdf->setPaper('a4', 'landscape');

        return $pdf->download("relatorio-{$month}.pdf");
    }
}