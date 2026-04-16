<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreEmployeeRequest;
use App\Http\Requests\UpdateEmployeeRequest;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

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

        return view('employees.show', compact('employee', 'workLogs'));
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
}