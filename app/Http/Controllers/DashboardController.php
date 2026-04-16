<?php

namespace App\Http\Controllers;

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

        $onLunch = $todayLogs->where('status', 'on_lunch')->count();
        $finished = $todayLogs->where('status', 'complete')->count();

        $weeklyHours = WorkLog::with('employee.user')
            ->whereBetween('work_date', [now()->startOfWeek(), now()->endOfWeek()])
            ->where('status', 'complete')
            ->get()
            ->groupBy('employee_id')
            ->map(fn($logs) => $logs->sum('minutes_worked'));

        return view('dashboard.gestor', compact(
            'totalEmployees',
            'todayLogs',
            'presentToday',
            'onLunch',
            'finished',
            'weeklyHours'
        ));
    }

    private function employeeDashboard($user)
    {
        $employee = $user->employee;

        if (!$employee) {
            return view('dashboard.employee', ['employee' => null]);
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

        return view('dashboard.employee', compact(
            'employee',
            'todayLog',
            'recentLogs',
            'monthMinutes',
            'weekMinutes'
        ));
    }
}