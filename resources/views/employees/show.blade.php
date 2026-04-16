@extends('layouts.app')
@section('title', 'Perfil do Funcionário')

@section('content')
<div class="space-y-6">

    <div class="flex items-center justify-between">
        <div>
            <a href="{{ route('employees.index') }}" class="text-sm text-indigo-600 hover:text-indigo-800">← Voltar</a>
            <h1 class="text-2xl font-bold text-gray-900 mt-2">{{ $employee->user->name }}</h1>
            <p class="text-gray-500 text-sm">{{ $employee->position }}</p>
        </div>
        <a href="{{ route('employees.edit', $employee) }}"
           class="px-4 py-2 text-sm border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition">
            Editar
        </a>
    </div>

    {{-- Dados cadastrais --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <h2 class="text-sm font-semibold text-gray-500 uppercase tracking-wide mb-4">Dados Cadastrais</h2>
        <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
            <div>
                <dt class="text-gray-500">E-mail</dt>
                <dd class="font-medium text-gray-800 mt-0.5">{{ $employee->user->email }}</dd>
            </div>
            <div>
                <dt class="text-gray-500">CPF</dt>
                <dd class="font-medium text-gray-800 font-mono mt-0.5">{{ $employee->cpf }}</dd>
            </div>
            <div>
                <dt class="text-gray-500">Data de admissão</dt>
                <dd class="font-medium text-gray-800 mt-0.5">{{ $employee->hired_at->format('d/m/Y') }}</dd>
            </div>
            <div>
                <dt class="text-gray-500">Endereço</dt>
                <dd class="font-medium text-gray-800 mt-0.5">{{ $employee->address }}</dd>
            </div>
        </dl>
    </div>

    {{-- Histórico de ponto --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100">
            <h2 class="text-base font-semibold text-gray-800">Histórico de Ponto</h2>
        </div>

        @if($workLogs->isEmpty())
            <div class="px-6 py-10 text-center text-gray-400 text-sm">Nenhum registro encontrado.</div>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-100 text-sm">
                    <thead class="bg-gray-50 text-xs font-medium text-gray-500 uppercase">
                        <tr>
                            <th class="px-6 py-3 text-left">Data</th>
                            <th class="px-6 py-3 text-left">Entrada</th>
                            <th class="px-6 py-3 text-left">Saída almoço</th>
                            <th class="px-6 py-3 text-left">Retorno</th>
                            <th class="px-6 py-3 text-left">Saída final</th>
                            <th class="px-6 py-3 text-left">Total</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @foreach($workLogs as $log)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-3 font-medium text-gray-700">
                                {{ $log->work_date->format('d/m/Y') }}
                            </td>
                            <td class="px-6 py-3 text-gray-600">{{ $log->clock_in?->format('H:i') ?? '—' }}</td>
                            <td class="px-6 py-3 text-gray-600">{{ $log->lunch_out?->format('H:i') ?? '—' }}</td>
                            <td class="px-6 py-3 text-gray-600">{{ $log->lunch_in?->format('H:i') ?? '—' }}</td>
                            <td class="px-6 py-3 text-gray-600">{{ $log->clock_out?->format('H:i') ?? '—' }}</td>
                            <td class="px-6 py-3 font-semibold text-indigo-600">{{ $log->formatted_hours }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="px-6 py-4 border-t border-gray-100">
                {{ $workLogs->links() }}
            </div>
        @endif
    </div>
</div>
@endsection