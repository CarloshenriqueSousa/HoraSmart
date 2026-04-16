@extends('layouts.app')
@section('title', 'Dashboard do Gestor')

@section('content')
<div class="space-y-6">

    {{-- Cabeçalho --}}
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Painel do Gestor</h1>
        <p class="text-gray-500 text-sm mt-1">{{ now()->format('l, d \d\e F \d\e Y') }}</p>
    </div>

    {{-- Métricas --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Total de Funcionários</p>
            <p class="text-3xl font-bold text-gray-900 mt-1">{{ $totalEmployees }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Presentes Hoje</p>
            <p class="text-3xl font-bold text-indigo-600 mt-1">{{ $presentToday }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">No Almoço</p>
            <p class="text-3xl font-bold text-yellow-500 mt-1">{{ $onLunch }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Jornada Completa</p>
            <p class="text-3xl font-bold text-green-600 mt-1">{{ $finished }}</p>
        </div>
    </div>

    {{-- Painel ao vivo --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
            <h2 class="text-base font-semibold text-gray-800">Situação em Tempo Real</h2>
            <span class="flex items-center gap-2 text-xs text-green-600 font-medium">
                <span class="inline-block w-2 h-2 rounded-full bg-green-400 animate-pulse"></span>
                Ao vivo
            </span>
        </div>

        @if($todayLogs->isEmpty())
            <div class="px-6 py-10 text-center text-gray-400 text-sm">
                Nenhum funcionário registrou ponto hoje.
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-100">
                    <thead class="bg-gray-50 text-xs font-medium text-gray-500 uppercase tracking-wide">
                        <tr>
                            <th class="px-6 py-3 text-left">Funcionário</th>
                            <th class="px-6 py-3 text-left">Cargo</th>
                            <th class="px-6 py-3 text-left">Entrada</th>
                            <th class="px-6 py-3 text-left">Almoço</th>
                            <th class="px-6 py-3 text-left">Retorno</th>
                            <th class="px-6 py-3 text-left">Saída</th>
                            <th class="px-6 py-3 text-left">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @foreach($todayLogs as $log)
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-6 py-3 text-sm font-medium text-gray-900">
                                {{ $log->employee->user->name }}
                            </td>
                            <td class="px-6 py-3 text-sm text-gray-500">
                                {{ $log->employee->position }}
                            </td>
                            <td class="px-6 py-3 text-sm text-gray-700">
                                {{ $log->clock_in?->format('H:i') ?? '—' }}
                            </td>
                            <td class="px-6 py-3 text-sm text-gray-700">
                                {{ $log->lunch_out?->format('H:i') ?? '—' }}
                            </td>
                            <td class="px-6 py-3 text-sm text-gray-700">
                                {{ $log->lunch_in?->format('H:i') ?? '—' }}
                            </td>
                            <td class="px-6 py-3 text-sm text-gray-700">
                                {{ $log->clock_out?->format('H:i') ?? '—' }}
                            </td>
                            <td class="px-6 py-3">
                                @php
                                    $statusMap = [
                                        'in_progress'     => ['label' => 'Trabalhando',    'class' => 'bg-indigo-100 text-indigo-700'],
                                        'on_lunch'        => ['label' => 'No almoço',       'class' => 'bg-yellow-100 text-yellow-700'],
                                        'back_from_lunch' => ['label' => 'Retornou',        'class' => 'bg-blue-100 text-blue-700'],
                                        'complete'        => ['label' => 'Finalizado',      'class' => 'bg-green-100 text-green-700'],
                                    ];
                                    $s = $statusMap[$log->status] ?? ['label' => $log->status, 'class' => 'bg-gray-100 text-gray-600'];
                                @endphp
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $s['class'] }}">
                                    {{ $s['label'] }}
                                </span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    {{-- Horas da semana --}}
    @if($weeklyHours->isNotEmpty())
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <h2 class="text-base font-semibold text-gray-800 mb-4">Horas Trabalhadas Esta Semana</h2>
        <div class="space-y-3">
            @foreach($weeklyHours as $employeeId => $minutes)
                @php
                    $log = $todayLogs->firstWhere('employee_id', $employeeId)
                        ?? \App\Models\WorkLog::with('employee.user')->where('employee_id', $employeeId)->first();
                    $name = $log?->employee?->user?->name ?? 'Funcionário #'.$employeeId;
                    $hours = intdiv($minutes, 60);
                    $mins  = $minutes % 60;
                    $pct   = min(100, round($minutes / (44 * 60) * 100));
                @endphp
                <div>
                    <div class="flex justify-between text-sm mb-1">
                        <span class="font-medium text-gray-700">{{ $name }}</span>
                        <span class="text-gray-500">{{ sprintf('%02d:%02d', $hours, $mins) }}</span>
                    </div>
                    <div class="w-full bg-gray-100 rounded-full h-2">
                        <div class="bg-indigo-500 h-2 rounded-full transition-all" style="width: {{ $pct }}%"></div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
    @endif

</div>
@endsection