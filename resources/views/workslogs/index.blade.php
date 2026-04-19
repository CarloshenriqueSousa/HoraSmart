{{--
    View: workslogs/index.blade.php — Lista de registros de ponto.

    Features:
     - Filtro por status (Alpine.js client-side)
     - Filtro por mês (input month nativo → query param)
     - Indicador de horas extras na tabela
     - Status badges com ícones SVG
     - Botões de exportação CSV/PDF (gestor only)

    Gestor: vê todos os registros de todos os funcionários
    Funcionário: vê apenas seus próprios registros

    Layout: layouts/app.blade.php
--}}
@extends('layouts.app')
@section('title', 'Registros de Ponto')

@section('content')
<div class="space-y-6" x-data="{ statusFilter: 'all' }">

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Registros de Ponto</h1>
            <p class="text-gray-500 text-sm mt-0.5">
                {{ auth()->user()->isGestor() ? 'Todos os funcionários' : 'Seus registros de jornada' }}
            </p>
        </div>

        @if(auth()->user()->isGestor())
        <div class="flex items-center gap-2">
            <a href="{{ route('worklogs.export.csv', ['month' => $month ?? date('Y-m')]) }}"
               class="inline-flex items-center gap-2 px-4 py-2 text-sm border border-gray-300 rounded-xl text-gray-700 hover:bg-gray-50 transition font-medium">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                CSV
            </a>
            <a href="{{ route('worklogs.export.pdf', ['month' => $month ?? date('Y-m')]) }}"
               class="inline-flex items-center gap-2 px-4 py-2 text-sm bg-gradient-to-r from-indigo-600 to-violet-600 text-white rounded-xl hover:from-indigo-700 hover:to-violet-700 transition font-medium shadow-md shadow-indigo-500/20">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                PDF
            </a>
        </div>
        @endif
    </div>

    {{-- Filtros --}}
    <div class="flex flex-col sm:flex-row gap-3 items-start sm:items-center flex-wrap">
        {{-- Filtro por mês --}}
        <form method="GET" id="filter-form" class="contents">
            <div class="flex items-center gap-2">
                <label class="text-sm text-gray-600 font-medium">Mês:</label>
                <input type="month" name="month" value="{{ $month ?? date('Y-m') }}"
                       form="filter-form"
                       onchange="document.getElementById('filter-form').submit()"
                       class="rounded-xl border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition">
            </div>

            @if(auth()->user()->isGestor() && $employees->isNotEmpty())
            <div class="flex items-center gap-2">
                <label class="text-sm text-gray-600 font-medium">Funcionário:</label>
                <select name="employee_id" form="filter-form"
                        onchange="document.getElementById('filter-form').submit()"
                        class="rounded-xl border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition">
                    <option value="">— Todos —</option>
                    @foreach($employees as $emp)
                        <option value="{{ $emp->id }}" {{ $employeeId == $emp->id ? 'selected' : '' }}>
                            {{ $emp->user->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            @endif
        </form>

        {{-- Filtro por status (client-side Alpine.js) --}}
        <div class="flex items-center gap-1 bg-gray-100 rounded-xl p-1">
            @php
                $statusFilters = [
                    'all'         => 'Todos',
                    'in_progress' => 'Em andamento',
                    'complete'    => 'Finalizados',
                ];
            @endphp
            @foreach($statusFilters as $key => $label)
            <button @click="statusFilter = '{{ $key }}'"
                    :class="statusFilter === '{{ $key }}' ? 'bg-white shadow-sm text-gray-900' : 'text-gray-500 hover:text-gray-700'"
                    class="px-3 py-1.5 text-xs font-medium rounded-lg transition-all">
                {{ $label }}
            </button>
            @endforeach
        </div>
    </div>

    {{-- Tabela --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        @if($logs->isEmpty())
            <div class="px-6 py-16 text-center">
                <svg class="w-12 h-12 text-gray-200 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
                <p class="text-gray-400 text-sm">Nenhum registro encontrado neste período.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-100 text-sm">
                    <thead class="bg-gray-50/80 text-xs font-medium text-gray-500 uppercase">
                        <tr>
                            @if(auth()->user()->isGestor())
                                <th class="px-6 py-3 text-left">Funcionário</th>
                            @endif
                            <th class="px-6 py-3 text-left">Data</th>
                            <th class="px-6 py-3 text-left">Entrada</th>
                            <th class="px-6 py-3 text-left">Almoço</th>
                            <th class="px-6 py-3 text-left">Retorno</th>
                            <th class="px-6 py-3 text-left">Saída</th>
                            <th class="px-6 py-3 text-left">Total</th>
                            <th class="px-6 py-3 text-left">Extras</th>
                            <th class="px-6 py-3 text-left">Status</th>
                            @if(auth()->user()->isGestor())
                                <th class="px-6 py-3 text-right">Ações</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @foreach($logs as $log)
                        <tr class="hover:bg-gray-50/50 transition"
                            x-show="statusFilter === 'all' || statusFilter === '{{ $log->status === 'complete' ? 'complete' : 'in_progress' }}'"
                            x-transition>
                            @if(auth()->user()->isGestor())
                                <td class="px-6 py-3 font-medium text-gray-800">{{ $log->employee->user->name }}</td>
                            @endif
                            <td class="px-6 py-3 font-medium text-gray-700">{{ $log->work_date->format('d/m/Y') }}</td>
                            <td class="px-6 py-3 text-gray-600 font-mono text-xs">{{ $log->clock_in?->format('H:i') ?? '—' }}</td>
                            <td class="px-6 py-3 text-gray-600 font-mono text-xs">{{ $log->lunch_out?->format('H:i') ?? '—' }}</td>
                            <td class="px-6 py-3 text-gray-600 font-mono text-xs">{{ $log->lunch_in?->format('H:i') ?? '—' }}</td>
                            <td class="px-6 py-3 text-gray-600 font-mono text-xs">{{ $log->clock_out?->format('H:i') ?? '—' }}</td>
                            <td class="px-6 py-3 font-semibold text-indigo-600 font-mono">{{ $log->formatted_hours }}</td>
                            <td class="px-6 py-3 font-mono text-xs {{ $log->overtime_minutes > 0 ? 'text-rose-600 font-semibold' : 'text-gray-400' }}">{{ $log->formatted_overtime }}</td>
                            <td class="px-6 py-3">
                                @php
                                    $sc = [
                                        'in_progress' => ['label' => 'Em andamento', 'class' => 'bg-blue-100 text-blue-700'],
                                        'on_lunch' => ['label' => 'Almoço', 'class' => 'bg-amber-100 text-amber-700'],
                                        'back_from_lunch' => ['label' => 'Retornou', 'class' => 'bg-indigo-100 text-indigo-700'],
                                        'complete' => ['label' => 'Completo', 'class' => 'bg-emerald-100 text-emerald-700'],
                                    ];
                                    $cfg = $sc[$log->status] ?? $sc['in_progress'];
                                @endphp
                                <span class="inline-flex px-2 py-0.5 rounded-lg text-xs font-medium {{ $cfg['class'] }}">{{ $cfg['label'] }}</span>
                            </td>
                            @if(auth()->user()->isGestor())
                                <td class="px-6 py-3">
                                    <div class="flex items-center justify-end gap-1">
                                        <a href="{{ route('worklogs.show', $log) }}" class="p-2 text-gray-400 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition" title="Ver detalhes">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                        </a>
                                        <a href="{{ route('worklogs.edit', $log) }}" class="p-2 text-gray-400 hover:text-amber-600 hover:bg-amber-50 rounded-lg transition" title="Editar">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                        </a>
                                    </div>
                                </td>
                            @endif
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="px-6 py-4 border-t border-gray-100">
                {{ $logs->links() }}
            </div>
        @endif
    </div>
</div>
@endsection