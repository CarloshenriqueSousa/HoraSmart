{{--
    View: dashboard/gestor.blade.php — Painel do Gestor de RH.

    Seções:
     1. Saudação personalizada com hora do dia
     2. KPI Cards (funcionários, presentes, almoço, finalizados)
     3. Card de ajustes pendentes + alerta de sobrecarga
     4. Tabela de presença do dia com status visual
     5. Gráfico de barras CSS das horas semanais por funcionário
     6. Botões de exportação CSV/PDF

    Dados: DashboardController::gestorDashboard()
    Auto-refresh: 60 segundos (meta refresh)

    Tecnologias: Blade, Tailwind CSS, Alpine.js, CSS Grid, CSS Transitions
    Layout: layouts/app.blade.php
--}}
@extends('layouts.app')
@section('title', 'Dashboard Gestor')

@section('content')
<div class="space-y-6" x-data>

    {{-- Saudação --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            @php
                $hour = now()->hour;
                $greeting = $hour < 12 ? 'Bom dia' : ($hour < 18 ? 'Boa tarde' : 'Boa noite');
            @endphp
            <h1 class="text-2xl font-bold text-gray-900">
                {{ $greeting }}, {{ explode(' ', auth()->user()->name)[0] }} 👋
            </h1>
            <p class="text-gray-500 text-sm mt-0.5">
                {{ now()->translatedFormat('l, d \d\e F \d\e Y') }} — Painel do Gestor
            </p>
        </div>
        <div class="flex items-center gap-2 no-print">
            <a href="{{ route('worklogs.export.csv', ['month' => date('Y-m')]) }}"
               class="inline-flex items-center gap-2 px-4 py-2 text-sm border border-gray-300 rounded-xl text-gray-700 hover:bg-gray-50 transition font-medium">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                CSV
            </a>
            <a href="{{ route('worklogs.export.pdf', ['month' => date('Y-m')]) }}"
               class="inline-flex items-center gap-2 px-4 py-2 text-sm bg-gradient-to-r from-indigo-600 to-violet-600 text-white rounded-xl hover:from-indigo-700 hover:to-violet-700 transition font-medium shadow-md shadow-indigo-500/20">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                PDF
            </a>
        </div>
    </div>

    {{-- KPI Cards --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 stagger-children">
        @php
            $kpis = [
                ['label' => 'Total Funcionários', 'value' => $totalEmployees, 'icon' => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z', 'color' => 'indigo', 'bg' => 'bg-indigo-50', 'text' => 'text-indigo-600', 'iconBg' => 'bg-indigo-100'],
                ['label' => 'Presentes Hoje', 'value' => $presentToday, 'icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z', 'color' => 'emerald', 'bg' => 'bg-emerald-50', 'text' => 'text-emerald-600', 'iconBg' => 'bg-emerald-100'],
                ['label' => 'No Almoço', 'value' => $onLunch, 'icon' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z', 'color' => 'amber', 'bg' => 'bg-amber-50', 'text' => 'text-amber-600', 'iconBg' => 'bg-amber-100'],
                ['label' => 'Finalizaram', 'value' => $finished, 'icon' => 'M5 13l4 4L19 7', 'color' => 'blue', 'bg' => 'bg-blue-50', 'text' => 'text-blue-600', 'iconBg' => 'bg-blue-100'],
            ];
        @endphp

        @foreach($kpis as $kpi)
        <div class="bg-white rounded-2xl border border-gray-100 p-5 shadow-sm card-hover">
            <div class="flex items-center justify-between mb-3">
                <div class="w-10 h-10 {{ $kpi['iconBg'] }} rounded-xl flex items-center justify-center">
                    <svg class="w-5 h-5 {{ $kpi['text'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $kpi['icon'] }}"/>
                    </svg>
                </div>
                @if($kpi['label'] === 'Presentes Hoje')
                    <span class="w-2.5 h-2.5 bg-emerald-400 rounded-full animate-pulse"></span>
                @endif
            </div>
            <p class="text-3xl font-bold text-gray-900">{{ $kpi['value'] }}</p>
            <p class="text-xs text-gray-500 mt-1 font-medium">{{ $kpi['label'] }}</p>
        </div>
        @endforeach
    </div>

    {{-- Alertas: Ajustes Pendentes + Sobrecarga --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        @if($pendingAdjustments > 0)
        <a href="{{ route('adjustments.index') }}" class="bg-amber-50 border border-amber-200 rounded-2xl p-5 card-hover group">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-amber-100 rounded-xl flex items-center justify-center">
                    <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/></svg>
                </div>
                <div>
                    <p class="text-amber-900 font-semibold text-sm">{{ $pendingAdjustments }} ajuste(s) pendente(s)</p>
                    <p class="text-amber-700 text-xs mt-0.5">Clique para revisar as solicitações</p>
                </div>
                <svg class="w-5 h-5 text-amber-400 ml-auto group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </div>
        </a>
        @endif

        @if($overloadedEmployees->isNotEmpty())
        <div class="bg-rose-50 border border-rose-200 rounded-2xl p-5">
            <div class="flex items-center gap-3 mb-3">
                <div class="w-10 h-10 bg-rose-100 rounded-xl flex items-center justify-center">
                    <svg class="w-5 h-5 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
                </div>
                <div>
                    <p class="text-rose-900 font-semibold text-sm">Alerta de sobrecarga</p>
                    <p class="text-rose-700 text-xs mt-0.5">Funcionários com muitas horas extras esta semana</p>
                </div>
            </div>
            <div class="space-y-2">
                @foreach($overloadedEmployees as $over)
                <div class="flex items-center justify-between bg-white rounded-xl px-3 py-2 text-sm">
                    <span class="text-gray-700 font-medium">{{ $over['employee']->user->name }}</span>
                    <span class="text-rose-600 font-semibold font-mono text-xs">+{{ $over['formatted'] }}</span>
                </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Tabela de Presença --}}
        <div class="lg:col-span-2 bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                <div>
                    <h2 class="text-base font-semibold text-gray-800">Presença Hoje</h2>
                    <p class="text-xs text-gray-400 mt-0.5">Status em tempo real dos funcionários</p>
                </div>
                <span class="flex items-center gap-1.5 text-xs text-gray-400">
                    <span class="w-1.5 h-1.5 bg-emerald-400 rounded-full animate-pulse"></span>
                    Atualiza a cada 60s
                </span>
            </div>

            @if($allEmployees->isEmpty())
                <div class="px-6 py-16 text-center">
                    <svg class="w-12 h-12 text-gray-200 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    <p class="text-gray-400 text-sm">Nenhum funcionário cadastrado.</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-100 text-sm">
                        <thead class="bg-gray-50/80 text-xs font-medium text-gray-500 uppercase">
                            <tr>
                                <th class="px-6 py-3 text-left">Funcionário</th>
                                <th class="px-6 py-3 text-left">Entrada</th>
                                <th class="px-6 py-3 text-left">Almoço</th>
                                <th class="px-6 py-3 text-left">Retorno</th>
                                <th class="px-6 py-3 text-left">Saída</th>
                                <th class="px-6 py-3 text-left">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            @foreach($allEmployees as $emp)
                            @php
                                $log = $todayLogsByEmployee[$emp->id] ?? null;
                                $colors = ['bg-indigo-100 text-indigo-600', 'bg-violet-100 text-violet-600', 'bg-emerald-100 text-emerald-600', 'bg-amber-100 text-amber-600', 'bg-rose-100 text-rose-600'];
                                $statusConfig = [
                                    'in_progress'     => ['label' => 'Trabalhando',  'class' => 'bg-emerald-100 text-emerald-700', 'dot' => 'bg-emerald-400 animate-pulse'],
                                    'on_lunch'        => ['label' => 'Almoço',       'class' => 'bg-amber-100 text-amber-700',   'dot' => 'bg-amber-400'],
                                    'back_from_lunch' => ['label' => 'Retornou',     'class' => 'bg-blue-100 text-blue-700',     'dot' => 'bg-blue-400 animate-pulse'],
                                    'complete'        => ['label' => 'Finalizado',   'class' => 'bg-gray-100 text-gray-600',     'dot' => 'bg-gray-400'],
                                ];
                                $config = $log ? ($statusConfig[$log->status] ?? $statusConfig['in_progress'])
                                               : ['label' => 'Não registrou', 'class' => 'bg-red-50 text-red-400', 'dot' => 'bg-red-300'];
                            @endphp
                            <tr class="hover:bg-gray-50/50 transition {{ !$log ? 'opacity-60' : '' }}">
                                <td class="px-6 py-3">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-lg {{ $colors[$emp->id % count($colors)] }} flex items-center justify-center text-xs font-bold">
                                            {{ strtoupper(substr($emp->user->name, 0, 2)) }}
                                        </div>
                                        <div>
                                            <p class="font-medium text-gray-800">{{ $emp->user->name }}</p>
                                            <p class="text-xs text-gray-400">{{ $emp->position }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-3 font-mono text-xs text-gray-600">{{ $log?->clock_in?->format('H:i')  ?? '—' }}</td>
                                <td class="px-6 py-3 font-mono text-xs text-gray-600">{{ $log?->lunch_out?->format('H:i') ?? '—' }}</td>
                                <td class="px-6 py-3 font-mono text-xs text-gray-600">{{ $log?->lunch_in?->format('H:i')  ?? '—' }}</td>
                                <td class="px-6 py-3 font-mono text-xs text-gray-600">{{ $log?->clock_out?->format('H:i') ?? '—' }}</td>
                                <td class="px-6 py-3">
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-medium {{ $config['class'] }}">
                                        <span class="w-1.5 h-1.5 rounded-full {{ $config['dot'] }}"></span>
                                        {{ $config['label'] }}
                                    </span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        {{-- Gráfico de Barras (Horas Semanais) --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
            <h2 class="text-base font-semibold text-gray-800 mb-1">Horas da Semana</h2>
            <p class="text-xs text-gray-400 mb-5">Total por funcionário (seg-sex)</p>

            @if($weeklyHours->isEmpty())
                <div class="py-12 text-center">
                    <p class="text-gray-300 text-sm">Sem dados esta semana.</p>
                </div>
            @else
                <div class="space-y-3.5">
                    @php $maxHours = $weeklyHours->max() ?: 1; @endphp
                    @foreach($weeklyHours as $empId => $minutes)
                        @php
                            $emp = $employees[$empId] ?? null;
                            $hours = round($minutes / 60, 1);
                            $pct = min(100, round(($minutes / $maxHours) * 100));
                            $overtime = max(0, $minutes - (480 * 5));
                        @endphp
                        @if($emp)
                        <div>
                            <div class="flex items-center justify-between mb-1">
                                <span class="text-xs text-gray-700 font-medium truncate pr-2">{{ $emp->user->name }}</span>
                                <span class="text-xs font-bold {{ $overtime > 0 ? 'text-rose-600' : 'text-indigo-600' }} font-mono whitespace-nowrap">
                                    {{ $hours }}h
                                    @if($overtime > 0)
                                        <span class="text-rose-400 font-normal">(+{{ round($overtime/60,1) }}h)</span>
                                    @endif
                                </span>
                            </div>
                            <div class="w-full h-2.5 bg-gray-100 rounded-full overflow-hidden">
                                <div class="h-full rounded-full transition-all duration-1000 ease-out {{ $overtime > 0 ? 'bg-gradient-to-r from-rose-400 to-rose-500' : 'bg-gradient-to-r from-indigo-400 to-violet-500' }}"
                                     style="width: {{ $pct }}%; --progress-width: {{ $pct }}%"
                                     class="animate-progress"></div>
                            </div>
                        </div>
                        @endif
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>

{{-- Auto-refresh --}}
<meta http-equiv="refresh" content="60">
@endsection