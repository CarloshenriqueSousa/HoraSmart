<?php

/**
 * Controller: WorkLogController — Registros de ponto (jornada de trabalho).
 *
 * Funcionalidades:
 *  - index(): Lista registros — gestor vê todos, funcionário vê apenas os seus.
 *             Suporta filtro por mês/período via query params (?month=2026-04)
 *  - punch(): Registra batida via AJAX (POST /punch) — retorna JSON para Alpine.js
 *  - show():  Detalhes de um registro com batidas e solicitações de ajuste
 *  - edit():  Formulário de edição (apenas gestor)
 *  - update(): Atualiza horários de um registro (apenas gestor)
 *  - export(): Exporta registros como CSV (StreamedResponse)
 *  - exportPdf(): Exporta relatório mensal como PDF (DomPDF)
 *
 * O método punch() é o coração do sistema de ponto. Ele delega para o WorkLogService
 * que controla a máquina de estados (entrada → almoço → retorno → saída).
 *
 * Segurança:
 *  - Policies verificam que funcionário só vê seus próprios registros
 *  - Middleware 'role:employee' protege a rota de punch
 *  - Middleware 'role:gestor' protege edição e exportação
 *
 * Tecnologias: Laravel Controller, WorkLogService (DI), Policies, JSON Response,
 *              AJAX, StreamedResponse (CSV), DomPDF (PDF)
 *
 * @see \App\Services\WorkLogService
 * @see \App\Policies\WorkLogPolicy
 * @see resources/views/workslogs/index.blade.php
 * @see resources/views/workslogs/show.blade.php
 * @see resources/views/workslogs/edit.blade.php
 */

namespace App\Http\Controllers;

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
        $month      = $request->query('month');       // formato: 2026-04
        $employeeId = $request->query('employee_id'); // filtro por funcionário (gestor)

        if ($user->isGestor()) {
            $query = WorkLog::with('employee.user');

            // Filtro por funcionário
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

        // Lista de funcionários para o select de filtro (apenas para gestores)
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

    /**
     * Formulário de edição de registro (apenas gestor).
     */
    public function edit(WorkLog $workLog)
    {
        $this->authorize('view', $workLog);
        $workLog->load('employee.user');
        return view('workslogs.edit', compact('workLog'));
    }

    /**
     * Atualiza horários de um registro (apenas gestor).
     */
    public function update(Request $request, WorkLog $workLog)
    {
        $validated = $request->validate([
            'clock_in'  => ['nullable', 'date'],
            'lunch_out' => ['nullable', 'date', 'after:clock_in'],
            'lunch_in'  => ['nullable', 'date', 'after:lunch_out'],
            'clock_out' => ['nullable', 'date', 'after:lunch_in'],
        ]);

        $workLog->update($validated);

        // Recalcular horas se jornada completa
        if ($workLog->clock_in && $workLog->lunch_out && $workLog->lunch_in && $workLog->clock_out) {
            $morning   = $workLog->lunch_out->diffInMinutes($workLog->clock_in);
            $afternoon = $workLog->clock_out->diffInMinutes($workLog->lunch_in);
            $workLog->update([
                'minutes_worked' => max(0, $morning + $afternoon),
                'status'         => 'complete',
            ]);
        }

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
            ->where('status', 'complete')
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
            ->where('status', 'complete')
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