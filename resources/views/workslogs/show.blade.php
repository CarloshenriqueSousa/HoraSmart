{{--
    View: workslogs/show.blade.php — Detalhes de um registro de ponto.

    Seções:
     1. Header com data, nome do funcionário e status
     2. Timeline vertical animada com 4 batidas
     3. Cards: Manhã, Almoço, Tarde, Total, Extras
     4. Histórico de ajustes solicitados (se houver)
     5. Botão solicitar ajuste (employee) / editar (gestor)

    Layout: layouts/app.blade.php
--}}
@extends('layouts.app')
@section('title', 'Registro de Ponto')

@section('content')
<div class="space-y-6">

    {{-- Header --}}
    <div class="flex items-start justify-between">
        <div>
            <a href="{{ route('worklogs.index') }}" class="inline-flex items-center gap-1 text-sm text-indigo-600 hover:text-indigo-800 font-medium transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                Voltar
            </a>
            <h1 class="text-2xl font-bold text-gray-900 mt-2">
                Registro de {{ $workLog->work_date->format('d/m/Y') }}
            </h1>
            <p class="text-gray-500 text-sm mt-0.5">
                {{ $workLog->employee->user->name }} — {{ $workLog->employee->position }}
            </p>
        </div>
        <div class="flex items-center gap-2">
            @if(auth()->user()->isGestor())
                <a href="{{ route('worklogs.edit', $workLog) }}"
                   class="inline-flex items-center gap-2 px-4 py-2 text-sm border border-gray-300 rounded-xl text-gray-700 hover:bg-gray-50 transition font-medium">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    Editar
                </a>
            @endif
            @if(auth()->user()->isEmployee() && $workLog->isComplete())
                <a href="{{ route('adjustments.create', ['work_log_id' => $workLog->id]) }}"
                   class="inline-flex items-center gap-2 px-4 py-2 text-sm bg-gradient-to-r from-indigo-600 to-violet-600 text-white rounded-xl hover:from-indigo-700 hover:to-violet-700 transition font-medium shadow-md shadow-indigo-500/20">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    Solicitar Ajuste
                </a>
            @endif
        </div>
    </div>

    {{-- Timeline das batidas --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
        <h2 class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-6">Batidas do Dia</h2>

        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 stagger-children">
            @php
                $beats = [
                    ['field' => 'clock_in',  'label' => 'Entrada',       'icon' => 'M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7', 'color' => 'emerald'],
                    ['field' => 'lunch_out', 'label' => 'Saída Almoço',  'icon' => 'M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4', 'color' => 'amber'],
                    ['field' => 'lunch_in',  'label' => 'Retorno',       'icon' => 'M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7', 'color' => 'blue'],
                    ['field' => 'clock_out', 'label' => 'Saída Final',   'icon' => 'M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4', 'color' => 'indigo'],
                ];
            @endphp

            @foreach($beats as $beat)
                @php $time = $workLog->{$beat['field']}; @endphp
                <div class="text-center p-4 rounded-2xl {{ $time ? 'bg-' . $beat['color'] . '-50 border border-' . $beat['color'] . '-200' : 'bg-gray-50 border border-gray-200' }} transition-all">
                    <div class="w-12 h-12 mx-auto rounded-xl {{ $time ? 'bg-' . $beat['color'] . '-100 text-' . $beat['color'] . '-600' : 'bg-gray-200 text-gray-400' }} flex items-center justify-center mb-3">
                        @if($time)
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        @else
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        @endif
                    </div>
                    <p class="text-sm font-medium {{ $time ? 'text-gray-800' : 'text-gray-400' }}">{{ $beat['label'] }}</p>
                    <p class="text-2xl font-bold font-mono mt-1 {{ $time ? 'text-' . $beat['color'] . '-600' : 'text-gray-300' }}">
                        {{ $time?->format('H:i') ?? '--:--' }}
                    </p>
                </div>
            @endforeach
        </div>
    </div>

    {{-- Cards de resumo --}}
    @if($workLog->isComplete())
    <div class="grid grid-cols-2 lg:grid-cols-5 gap-4">
        @php
            $morningH = sprintf('%02d:%02d', intdiv($workLog->morning_minutes, 60), $workLog->morning_minutes % 60);
            $lunchH = sprintf('%02d:%02d', intdiv($workLog->lunch_minutes, 60), $workLog->lunch_minutes % 60);
            $afternoonH = sprintf('%02d:%02d', intdiv($workLog->afternoon_minutes, 60), $workLog->afternoon_minutes % 60);
            $cards = [
                ['label' => 'Manhã',  'value' => $morningH,   'icon' => '☀️'],
                ['label' => 'Almoço', 'value' => $lunchH,     'icon' => '🍽️'],
                ['label' => 'Tarde',  'value' => $afternoonH, 'icon' => '🌤️'],
                ['label' => 'Total',  'value' => $workLog->formatted_hours, 'icon' => '⏱️'],
                ['label' => 'Extras', 'value' => $workLog->formatted_overtime, 'icon' => $workLog->overtime_minutes > 0 ? '🔴' : '✅'],
            ];
        @endphp
        @foreach($cards as $card)
        <div class="bg-white rounded-2xl border border-gray-100 p-4 shadow-sm text-center card-hover">
            <p class="text-lg mb-1">{{ $card['icon'] }}</p>
            <p class="text-xl font-bold text-gray-900 font-mono">{{ $card['value'] }}</p>
            <p class="text-xs text-gray-500 mt-0.5">{{ $card['label'] }}</p>
        </div>
        @endforeach
    </div>
    @endif

    {{-- Ajustes solicitados --}}
    @if($workLog->adjustments->isNotEmpty())
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100">
            <h2 class="text-base font-semibold text-gray-800">Ajustes Solicitados</h2>
        </div>
        <div class="divide-y divide-gray-50">
            @foreach($workLog->adjustments as $adj)
            <div class="px-6 py-4">
                <div class="flex items-center justify-between mb-2">
                    <div class="flex items-center gap-2">
                        @php
                            $fieldLabels = ['clock_in' => 'Entrada', 'lunch_out' => 'Saída Almoço', 'lunch_in' => 'Retorno', 'clock_out' => 'Saída Final'];
                            $adjStatus = [
                                'pending' => ['label' => 'Pendente', 'class' => 'bg-amber-100 text-amber-700'],
                                'approved' => ['label' => 'Aprovado', 'class' => 'bg-emerald-100 text-emerald-700'],
                                'rejected' => ['label' => 'Rejeitado', 'class' => 'bg-red-100 text-red-700'],
                            ];
                            $as = $adjStatus[$adj->status] ?? $adjStatus['pending'];
                        @endphp
                        <span class="text-sm font-medium text-gray-800">{{ $fieldLabels[$adj->field] ?? $adj->field }}</span>
                        <span class="text-xs text-gray-400">→</span>
                        <span class="text-sm font-mono font-semibold text-indigo-600">{{ \Carbon\Carbon::parse($adj->requested_time)->format('H:i') }}</span>
                    </div>
                    <span class="inline-flex px-2 py-0.5 rounded-lg text-xs font-medium {{ $as['class'] }}">{{ $as['label'] }}</span>
                </div>
                <p class="text-xs text-gray-500">{{ $adj->reason }}</p>
                @if($adj->reviewer_comment)
                    <p class="text-xs text-gray-400 mt-1 italic">Resposta: {{ $adj->reviewer_comment }}</p>
                @endif
            </div>
            @endforeach
        </div>
    </div>
    @endif

</div>
@endsection