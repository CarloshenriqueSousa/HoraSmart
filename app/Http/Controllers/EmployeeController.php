<?php

/**
 * Controller: EmployeeController — CRUD de funcionários (apenas gestores).
 *
 * Funcionalidades:
 *  - index():   Lista todos os funcionários com paginação
 *  - create():  Formulário de cadastro
 *  - store():   Cria User + Employee em transação (atomicidade garantida)
 *  - show():    Perfil do funcionário com histórico de ponto paginado + stats
 *  - edit():    Formulário de edição
 *  - update():  Atualiza User + Employee em transação
 *  - destroy(): Remove funcionário (cascade deleta User → Employee → WorkLogs)
 *  - export():  Exporta lista de funcionários como CSV
 *
 * Segurança:
 *  - Middleware 'role:gestor' protege todas as rotas (definido no web.php)
 *  - EmployeePolicy como camada adicional de autorização
 *  - StoreEmployeeRequest / UpdateEmployeeRequest para validação
 *
 * Design: Usa DB::transaction no store/update para garantir consistência
 * entre as tabelas users e employees (se uma falhar, ambas são revertidas).
 *
 * Tecnologias: Laravel Resource Controller, Form Requests, DB Transaction,
 *              Policies, StreamedResponse (CSV)
 *
 * @see \App\Http\Requests\StoreEmployeeRequest
 * @see \App\Http\Requests\UpdateEmployeeRequest
 * @see \App\Policies\EmployeePolicy
 */

namespace App\Http\Controllers;

use App\Http\Requests\StoreEmployeeRequest;
use App\Http\Requests\UpdateEmployeeRequest;
use App\Models\Employee;
use App\Models\User;
use App\Models\WorkLog;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpFoundation\StreamedResponse;

class EmployeeController extends Controller
{
    use AuthorizesRequests;

    public function index()
    {
        $this->authorize('viewAny', Employee::class);

        $employees = Employee::with('user')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('employees.index', compact('employees'));
    }

    public function create()
    {
        $this->authorize('create', Employee::class);
        return view('employees.create');
    }

    public function store(StoreEmployeeRequest $request)
    {
        DB::transaction(function () use ($request) {
            $user = User::create([
                'name'     => $request->name,
                'email'    => $request->email,
                'password' => Hash::make($request->password),
                'role'     => 'employee',
            ]);

            Employee::create([
                'user_id'  => $user->id,
                'cpf'      => $request->cpf,
                'address'  => $request->address,
                'position' => $request->position,
                'employee_type' => $request->employee_type,
                'shift'         => $request->shift,
                'daily_workload'=> $request->daily_workload,
                'overtime_tolerance' => $request->overtime_tolerance,
                'hired_at' => $request->hired_at,
            ]);
        });

        return redirect()->route('employees.index')
            ->with('success', 'Funcionário cadastrado com sucesso.');
    }

    public function show(Employee $employee)
    {
        $this->authorize('view', $employee);

        $employee->load('user');

        $workLogs = $employee->workLogs()
            ->orderByDesc('work_date')
            ->paginate(20);

        // Stats do funcionário
        $completeLogs    = $employee->workLogs()->where('status', 'complete');
        $totalDaysWorked = (clone $completeLogs)->count();
        $avgMinutes      = $totalDaysWorked > 0 ? (int) (clone $completeLogs)->avg('minutes_worked') : 0;

        // Horas extras calculadas via SQL usando a carga horária dinâmica
        $totalOvertimeRaw = (clone $completeLogs)
            ->selectRaw('SUM(CASE WHEN minutes_worked > ? THEN minutes_worked - ? ELSE 0 END) as total_overtime', [$employee->daily_workload, $employee->daily_workload])
            ->value('total_overtime');
        $totalOvertime = (int) $totalOvertimeRaw;

        // Horas no mês atual
        $monthMinutes = $employee->workLogs()
            ->whereMonth('work_date', now()->month)
            ->whereYear('work_date', now()->year)
            ->where('status', 'complete')
            ->sum('minutes_worked');

        return view('employees.show', compact(
            'employee', 'workLogs', 'totalDaysWorked',
            'avgMinutes', 'totalOvertime', 'monthMinutes'
        ));
    }

    public function edit(Employee $employee)
    {
        $this->authorize('update', $employee);
        $employee->load('user');
        return view('employees.edit', compact('employee'));
    }

    public function update(UpdateEmployeeRequest $request, Employee $employee)
    {
        DB::transaction(function () use ($request, $employee) {
            $employee->user->update([
                'name'  => $request->name,
                'email' => $request->email,
            ]);

            $employee->update([
                'cpf'      => $request->cpf,
                'address'  => $request->address,
                'position' => $request->position,
                'employee_type' => $request->employee_type,
                'shift'         => $request->shift,
                'daily_workload'=> $request->daily_workload,
                'overtime_tolerance' => $request->overtime_tolerance,
                'hired_at' => $request->hired_at,
            ]);
        });

        return redirect()->route('employees.index')
            ->with('success', 'Funcionário atualizado com sucesso.');
    }

    public function destroy(Employee $employee)
    {
        $this->authorize('delete', $employee);
        $employee->user->delete();

        return redirect()->route('employees.index')
            ->with('success', 'Funcionário removido com sucesso.');
    }

    /**
     * Exporta lista de funcionários como CSV.
     */
    public function export(): StreamedResponse
    {
        $employees = Employee::with('user')->get()->sortBy('user.name');

        return response()->streamDownload(function () use ($employees) {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF");

            fputcsv($handle, ['Nome', 'E-mail', 'CPF', 'Cargo', 'Endereço', 'Admissão'], ';');

            foreach ($employees as $emp) {
                fputcsv($handle, [
                    $emp->user->name,
                    $emp->user->email,
                    $emp->cpf,
                    $emp->position,
                    $emp->address,
                    $emp->hired_at?->format('d/m/Y'),
                ], ';');
            }

            fclose($handle);
        }, 'funcionarios-' . date('Y-m-d') . '.csv', [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
}