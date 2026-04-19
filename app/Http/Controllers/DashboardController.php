<?php

/**
 * Controller: DashboardController — Painel principal do sistema.
 *
 * Redireciona automaticamente para o dashboard correto baseado no role do usuário:
 *  - Gestor:      Dashboard com KPIs (total funcionários, presentes hoje, no almoço, finalizados)
 *                 + tabela de presença do dia + horas semanais por funcionário
 *                 + ajustes pendentes + alerta de sobrecarga
 *  - Funcionário: Dashboard pessoal com horas do dia/semana/mês + widget de ponto via AJAX
 *                 + banco de horas + horas extras + progress ring
 *
 * Dados calculados:
 *  - weeklyHours: horas trabalhadas na semana por funcionário (para gráfico de barras)
 *  - monthMinutes/weekMinutes: acumulados para o funcionário logado
 *  - overtimeWeek: horas extras na semana
 *  - pendingAdjustments: contagem de ajustes pendentes (gestor)
 *  - overloadedEmployees: funcionários com muitas horas extras (gestor)
 *
 * Tecnologias: Laravel Controller, Eloquent (with, whereBetween, groupBy), Carbon
 *
 * @see \App\Models\WorkLog
 * @see \App\Models\Employee
 * @see \App\Models\ClockAdjustment
 * @see resources/views/dashboard/gestor.blade.php
 * @see resources/views/dashboard/employee.blade.php
 */

namespace App\Http\Controllers;

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
            'in_progress', 'on_lunch', 'back_from_lunch', 'complete'
        ])->count();

        $onLunch  = $todayLogs->where('status', 'on_lunch')->count();
        $finished = $todayLogs->where('status', 'complete')->count();

        // Mapa employee_id => log de hoje (para lookup rápido na view)
        $todayLogsByEmployee = $todayLogs->keyBy('employee_id');

        // Todos os funcionários (para mostrar ausentes também)
        $allEmployees = Employee::with('user')->orderBy('created_at')->get();

        // Horas trabalhadas na semana por funcionário
        $weeklyHours = WorkLog::with('employee.user')
            ->whereBetween('work_date', [now()->startOfWeek(), now()->endOfWeek()])
            ->where('status', 'complete')
            ->get()
            ->groupBy('employee_id')
            ->map(fn($logs) => $logs->sum('minutes_worked'));

        $employees = $allEmployees->keyBy('id');

        // Ajustes pendentes
        $pendingAdjustments = ClockAdjustment::where('status', 'pending')->count();

        // Funcionários em sobrecarga (>2h extras na semana)
        $overloadedEmployees = collect();
        foreach ($weeklyHours as $employeeId => $minutes) {
            $overtime = max(0, $minutes - (WorkLog::DAILY_WORKLOAD * 5));
            if ($overtime > 120) {
                $emp = $employees[$employeeId] ?? null;
                if ($emp) {
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

        $monthMinutes = $employee->workLogs()
            ->whereMonth('work_date', now()->month)
            ->whereYear('work_date', now()->year)
            ->where('status', 'complete')
            ->sum('minutes_worked');

        $weekMinutes = $employee->workLogs()
            ->whereBetween('work_date', [now()->startOfWeek(), now()->endOfWeek()])
            ->where('status', 'complete')
            ->sum('minutes_worked');

        // Horas extras na semana
        $weekLogs = $employee->workLogs()
            ->whereBetween('work_date', [now()->startOfWeek(), now()->endOfWeek()])
            ->where('status', 'complete')
            ->get();
        $overtimeWeek = $weekLogs->sum(fn($log) => $log->overtime_minutes);

        // Horas extras no mês
        $monthLogs = $employee->workLogs()
            ->whereMonth('work_date', now()->month)
            ->whereYear('work_date', now()->year)
            ->where('status', 'complete')
            ->get();
        $overtimeMonth = $monthLogs->sum(fn($log) => $log->overtime_minutes);

        // Dias trabalhados no mês
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