<?php

/**
 * Controller: DashboardController — Painel principal do sistema.
 *
 * Redireciona para o dashboard correto baseado no role do usuário:
 *  - Gestor:      Dashboard com KPIs + tabela de presença + horas semanais + alerts
 *  - Funcionário: Dashboard pessoal com horas do dia/semana/mês + widget de ponto
 *
 * @see \App\Enums\WorkLogStatus
 * @see resources/views/dashboard/gestor.blade.php
 * @see resources/views/dashboard/employee.blade.php
 */

namespace App\Http\Controllers;

use App\Enums\WorkLogStatus;
use App\Models\ClockAdjustment;
use App\Models\Employee;
use App\Models\WorkLog;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        if ($user->isGestor()) {
            return $this->gestorDashboard();
        }

        return $this->employeeDashboard($user);
    }

    private function gestorDashboard()
    {
        $totalEmployees = Employee::count();

        $todayLogs = WorkLog::with('employee.user')
            ->whereDate('work_date', today())
            ->get();

        $presentToday = $todayLogs->whereIn('status', [
            WorkLogStatus::InProgress,
            WorkLogStatus::OnLunch,
            WorkLogStatus::BackFromLunch,
            WorkLogStatus::Complete,
        ])->count();

        $onLunch  = $todayLogs->where('status', WorkLogStatus::OnLunch)->count();
        $finished = $todayLogs->where('status', WorkLogStatus::Complete)->count();

        // Mapa employee_id => log de hoje (para lookup rápido na view)
        $todayLogsByEmployee = $todayLogs->keyBy('employee_id');

        // Todos os funcionários (para mostrar ausentes também)
        $allEmployees = Employee::with('user')->orderBy('created_at')->get();

        // Horas trabalhadas na semana por funcionário (agregação SQL — sem carregar tudo em RAM)
        $weekStart = now()->copy()->startOfWeek();
        $weekEnd   = now()->copy()->endOfWeek();

        $weeklyHours = WorkLog::selectRaw('employee_id, SUM(minutes_worked) as total')
            ->whereBetween('work_date', [$weekStart, $weekEnd])
            ->where('status', WorkLogStatus::Complete)
            ->groupBy('employee_id')
            ->pluck('total', 'employee_id');

        $employees = $allEmployees->keyBy('id');

        // Ajustes pendentes
        $pendingAdjustments = ClockAdjustment::where('status', 'pending')->count();

        // Funcionários em sobrecarga (>2h extras na semana)
        $overloadedEmployees = collect();
        foreach ($weeklyHours as $employeeId => $minutes) {
            $emp = $employees[$employeeId] ?? null;
            if ($emp) {
                $workload = $emp->daily_workload ?? 480;
                $overtime = max(0, $minutes - ($workload * 5));

                if ($overtime > 120) {
                    $overloadedEmployees->push([
                        'employee'  => $emp,
                        'overtime'  => $overtime,
                        'formatted' => sprintf('%02d:%02d', intdiv($overtime, 60), $overtime % 60),
                    ]);
                }
            }
        }

        return view('dashboard.gestor', compact(
            'totalEmployees',
            'todayLogs',
            'todayLogsByEmployee',
            'allEmployees',
            'presentToday',
            'onLunch',
            'finished',
            'weeklyHours',
            'employees',
            'pendingAdjustments',
            'overloadedEmployees'
        ));
    }

    private function employeeDashboard($user)
    {
        $employee = $user->employee;

        if (!$employee) {
            return view('dashboard.employee', [
                'employee'      => null,
                'todayLog'      => null,
                'recentLogs'    => collect(),
                'monthMinutes'  => 0,
                'weekMinutes'   => 0,
                'overtimeWeek'  => 0,
                'overtimeMonth' => 0,
                'workingDaysMonth' => 0,
            ]);
        }

        $todayLog = $employee->todayLog;

        $recentLogs = $employee->workLogs()
            ->orderByDesc('work_date')
            ->limit(7)
            ->get();

        // Dados da semana — query única
        $weekStart = now()->copy()->startOfWeek();
        $weekEnd   = now()->copy()->endOfWeek();

        $weekLogs = $employee->workLogs()
            ->whereBetween('work_date', [$weekStart, $weekEnd])
            ->where('status', WorkLogStatus::Complete)
            ->get();

        $weekMinutes  = $weekLogs->sum('minutes_worked');
        $overtimeWeek = $weekLogs->sum(fn($log) => $log->overtime_minutes);

        // Dados do mês — query única
        $monthLogs = $employee->workLogs()
            ->whereMonth('work_date', now()->month)
            ->whereYear('work_date', now()->year)
            ->where('status', WorkLogStatus::Complete)
            ->get();

        $monthMinutes     = $monthLogs->sum('minutes_worked');
        $overtimeMonth    = $monthLogs->sum(fn($log) => $log->overtime_minutes);
        $workingDaysMonth = $monthLogs->count();

        return view('dashboard.employee', compact(
            'employee',
            'todayLog',
            'recentLogs',
            'monthMinutes',
            'weekMinutes',
            'overtimeWeek',
            'overtimeMonth',
            'workingDaysMonth'
        ));
    }
}