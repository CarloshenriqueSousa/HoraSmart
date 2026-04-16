@extends('layouts.app')
@section('title', 'Funcionários')

@section('content')
<div class="space-y-6">

    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Funcionários</h1>
            <p class="text-gray-500 text-sm mt-1">{{ $employees->total() }} cadastrado(s)</p>
        </div>
        <a href="{{ route('employees.create') }}"
           class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 transition">
            + Novo Funcionário
        </a>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        @if($employees->isEmpty())
            <div class="px-6 py-16 text-center text-gray-400 text-sm">
                Nenhum funcionário cadastrado ainda.
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-100 text-sm">
                    <thead class="bg-gray-50 text-xs font-medium text-gray-500 uppercase tracking-wide">
                        <tr>
                            <th class="px-6 py-3 text-left">Nome</th>
                            <th class="px-6 py-3 text-left">E-mail</th>
                            <th class="px-6 py-3 text-left">Cargo</th>
                            <th class="px-6 py-3 text-left">CPF</th>
                            <th class="px-6 py-3 text-left">Admissão</th>
                            <th class="px-6 py-3 text-left">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @foreach($employees as $employee)
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-6 py-3 font-medium text-gray-900">
                                {{ $employee->user->name }}
                            </td>
                            <td class="px-6 py-3 text-gray-500">{{ $employee->user->email }}</td>
                            <td class="px-6 py-3 text-gray-700">{{ $employee->position }}</td>
                            <td class="px-6 py-3 text-gray-500 font-mono text-xs">{{ $employee->cpf }}</td>
                            <td class="px-6 py-3 text-gray-500">{{ $employee->hired_at->format('d/m/Y') }}</td>
                            <td class="px-6 py-3">
                                <div class="flex items-center gap-3">
                                    <a href="{{ route('employees.show', $employee) }}"
                                       class="text-indigo-600 hover:text-indigo-800 font-medium transition">Ver</a>
                                    <a href="{{ route('employees.edit', $employee) }}"
                                       class="text-gray-500 hover:text-gray-700 transition">Editar</a>
                                    <form method="POST" action="{{ route('employees.destroy', $employee) }}"
                                          onsubmit="return confirm('Remover {{ $employee->user->name }}? Esta ação não pode ser desfeita.')">
                                        @csrf @method('DELETE')
                                        <button type="submit"
                                            class="text-red-500 hover:text-red-700 transition">Remover</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="px-6 py-4 border-t border-gray-100">
                {{ $employees->links() }}
            </div>
        @endif
    </div>
</div>
@endsection