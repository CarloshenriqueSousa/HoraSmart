{{--
    View: employees/show.blade.php — Perfil detalhado de um funcionário.

    Seções:
     1. Header com avatar, nome, cargo, botões editar/voltar
     2. Stats rápidos (Dias trabalhados, Média/dia, Horas extras, Horas mês)
     3. Dados cadastrais (e-mail, CPF, admissão, endereço)
     4. Histórico de ponto paginado com horas extras

    Acesso: Gestores (via EmployeePolicy)
    Layout: layouts/app.blade.php
--}}
@extends('layouts.app')
@section('title', $employee->user->name)

@section('content')
<div class="space-y-6">

    <div class="flex items-start justify-between">
        <div>
            <a href="{{ route('employees.index') }}" class="inline-flex items-center gap-1 text-sm text-indigo-600 hover:text-indigo-800 font-medium transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                Voltar
            </a>
            <div class="flex items-center gap-4 mt-3">
                @php
                    $colors = ['bg-indigo-100 text-indigo-600', 'bg-violet-100 text-violet-600', 'bg-emerald-100 text-emerald-600', 'bg-amber-100 text-amber-600', 'bg-rose-100 text-rose-600'];
                    $colorClass = $colors[$employee->id % count($colors)];
                @endphp
                <div class="w-16 h-16 rounded-2xl {{ $colorClass }} flex items-center justify-center text-2xl font-bold shadow-sm">
                    {{ strtoupper(substr($employee->user->name, 0, 2)) }}
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">{{ $employee->user->name }}</h1>
                    <p class="text-gray-500 text-sm">{{ $employee->position }}</p>
                </div>
            </div>
        </div>
        <a href="{{ route('employees.edit', $employee) }}"
           class="inline-flex items-center gap-2 px-4 py-2 text-sm border border-gray-300 rounded-xl text-gray-700 hover:bg-gray-50 transition font-medium">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
            Editar
        </a>
    </div>

    {{-- Stats Rápidos --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 stagger-children">
        @php
            $avgFormatted = sprintf('%02d:%02d', intdiv($avgMinutes, 60), $avgMinutes % 60);
            $overtimeFormatted = sprintf('%02d:%02d', intdiv($totalOvertime, 60), $totalOvertime % 60);
            $monthFormatted = sprintf('%02d:%02d', intdiv($monthMinutes, 60), $monthMinutes % 60);

            $stats = [
                ['label' => 'Dias Trabalhados', 'value' => $totalDaysWorked, 'sub' => 'total registrado', 'icon' => 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z', 'iconBg' => 'bg-indigo-100', 'iconText' => 'text-indigo-600'],
                ['label' => 'Média Diária', 'value' => $avgFormatted, 'sub' => 'horas/dia', 'icon' => 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z', 'iconBg' => 'bg-violet-100', 'iconText' => 'text-violet-600'],
                ['label' => 'Horas Extras', 'value' => $overtimeFormatted, 'sub' => 'acumuladas', 'icon' => 'M13 7h8m0 0v8m0-8l-8 8-4-4-6 6', 'iconBg' => 'bg-rose-100', 'iconText' => 'text-rose-600'],
                ['label' => 'Horas no Mês', 'value' => $monthFormatted, 'sub' => now()->translatedFormat('F'), 'icon' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z', 'iconBg' => 'bg-blue-100', 'iconText' => 'text-blue-600'],
            ];
        @endphp

        @foreach($stats as $stat)
        <div class="bg-white rounded-2xl border border-gray-100 p-5 shadow-sm card-hover">
            <div class="w-9 h-9 {{ $stat['iconBg'] }} rounded-xl flex items-center justify-center mb-3">
                <svg class="w-4.5 h-4.5 {{ $stat['iconText'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $stat['icon'] }}"/>
                </svg>
            </div>
            <p class="text-2xl font-bold text-gray-900 font-mono">{{ $stat['value'] }}</p>
            <p class="text-xs text-gray-500 mt-0.5">{{ $stat['label'] }} · {{ $stat['sub'] }}</p>
        </div>
        @endforeach
    </div>

    {{-- Dados cadastrais --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
        <h2 class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-4">Dados Cadastrais</h2>
        <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
            <div class="bg-gray-50 rounded-xl p-3">
                <dt class="text-gray-500 text-xs">E-mail</dt>
                <dd class="font-medium text-gray-800 mt-0.5">{{ $employee->user->email }}</dd>
            </div>
            <div class="bg-gray-50 rounded-xl p-3">
                <dt class="text-gray-500 text-xs">CPF</dt>
                <dd class="font-medium text-gray-800 font-mono mt-0.5">{{ $employee->cpf }}</dd>
            </div>
            <div class="bg-gray-50 rounded-xl p-3">
                <dt class="text-gray-500 text-xs">Data de admissão</dt>
                <dd class="font-medium text-gray-800 mt-0.5">{{ $employee->hired_at->format('d/m/Y') }}</dd>
            </div>
            <div class="bg-gray-50 rounded-xl p-3">
                <dt class="text-gray-500 text-xs">Endereço</dt>
                <dd class="font-medium text-gray-800 mt-0.5">{{ $employee->address }}</dd>
            </div>
        </dl>
    </div>

    {{-- Histórico de ponto --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100">
            <h2 class="text-base font-semibold text-gray-800">Histórico de Ponto</h2>
            <p class="text-xs text-gray-400 mt-0.5">Todos os registros de jornada</p>
        </div>

        @if($workLogs->isEmpty())
            <div class="px-6 py-16 text-center">
                <svg class="w-12 h-12 text-gray-200 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
                <p class="text-gray-400 text-sm">Nenhum registro encontrado.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-100 text-sm">
                    <thead class="bg-gray-50/80 text-xs font-medium text-gray-500 uppercase">
                        <tr>
                            <th class="px-6 py-3 text-left">Data</th>
                            <th class="px-6 py-3 text-left">Entrada</th>
                            <th class="px-6 py-3 text-left">Saída almoço</th>
                            <th class="px-6 py-3 text-left">Retorno</th>
                            <th class="px-6 py-3 text-left">Saída final</th>
                            <th class="px-6 py-3 text-left">Total</th>
                            <th class="px-6 py-3 text-left">Extras</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @foreach($workLogs as $log)
                        <tr class="hover:bg-gray-50/50 transition cursor-pointer" onclick="window.location='{{ route('worklogs.show', $log) }}'">
                            <td class="px-6 py-3 font-medium text-gray-700">{{ $log->work_date->format('d/m/Y') }}</td>
                            <td class="px-6 py-3 text-gray-600 font-mono text-xs">{{ $log->clock_in?->format('H:i') ?? '—' }}</td>
                            <td class="px-6 py-3 text-gray-600 font-mono text-xs">{{ $log->lunch_out?->format('H:i') ?? '—' }}</td>
                            <td class="px-6 py-3 text-gray-600 font-mono text-xs">{{ $log->lunch_in?->format('H:i') ?? '—' }}</td>
                            <td class="px-6 py-3 text-gray-600 font-mono text-xs">{{ $log->clock_out?->format('H:i') ?? '—' }}</td>
                            <td class="px-6 py-3 font-semibold text-indigo-600 font-mono">{{ $log->formatted_hours }}</td>
                            <td class="px-6 py-3 font-mono text-xs {{ $log->overtime_minutes > 0 ? 'text-rose-600 font-semibold' : 'text-gray-400' }}">{{ $log->formatted_overtime }}</td>
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