{{--
    View: adjustments/create.blade.php — Formulário de solicitação de ajuste de ponto.

    Mostra os horários atuais do registro e solicita:
     - Campo a corrigir (select com as 4 opções)
     - Horário correto (datetime-local)
     - Justificativa (textarea, mín. 10 caracteres)

    Features:
     - Preview dinâmico antes → depois (Alpine.js)
     - Ícones nos inputs
     - Feedback visual

    Layout: layouts/app.blade.php
--}}
@extends('layouts.app')
@section('title', 'Solicitar Ajuste')

@section('content')
<div class="max-w-lg mx-auto space-y-6" x-data="{
    field: '{{ old('field', '') }}',
    get currentTime() {
        const times = {
            clock_in: '{{ $workLog->clock_in?->format('H:i') ?? '--:--' }}',
            lunch_out: '{{ $workLog->lunch_out?->format('H:i') ?? '--:--' }}',
            lunch_in: '{{ $workLog->lunch_in?->format('H:i') ?? '--:--' }}',
            clock_out: '{{ $workLog->clock_out?->format('H:i') ?? '--:--' }}',
        };
        return times[this.field] || '--:--';
    }
}">

    <div>
        <a href="{{ route('worklogs.show', $workLog) }}" class="inline-flex items-center gap-1 text-sm text-indigo-600 hover:text-indigo-800 font-medium transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            Voltar ao registro
        </a>
        <h1 class="text-2xl font-bold text-gray-900 mt-2">Solicitar Ajuste de Ponto</h1>
        <p class="text-gray-500 text-sm mt-1">
            Registro de {{ $workLog->work_date->format('d/m/Y') }}
        </p>
    </div>

    {{-- Horários atuais --}}
    <div class="bg-gray-50 rounded-2xl border border-gray-200 p-5">
        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-3">Horários Registrados</p>
        <div class="grid grid-cols-2 gap-3">
            @foreach(['clock_in' => 'Entrada', 'lunch_out' => 'Saída almoço', 'lunch_in' => 'Retorno', 'clock_out' => 'Saída final'] as $field => $label)
            <div class="flex items-center justify-between bg-white rounded-xl px-3 py-2 border border-gray-100">
                <span class="text-xs text-gray-500">{{ $label }}</span>
                <span class="text-sm font-mono font-semibold {{ $workLog->$field ? 'text-gray-800' : 'text-gray-300' }}">
                    {{ $workLog->$field?->format('H:i') ?? '--:--' }}
                </span>
            </div>
            @endforeach
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
        <form method="POST" action="{{ route('adjustments.store') }}" class="space-y-5" x-data="{ loading: false }" @submit="loading = true">
            @csrf
            <input type="hidden" name="work_log_id" value="{{ $workLog->id }}">

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Campo a corrigir</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
                    </div>
                    <select name="field" x-model="field"
                            class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-gray-300 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition @error('field') border-red-400 @enderror">
                        <option value="">Selecione...</option>
                        <option value="clock_in"  {{ old('field') === 'clock_in'  ? 'selected' : '' }}>Entrada</option>
                        <option value="lunch_out" {{ old('field') === 'lunch_out' ? 'selected' : '' }}>Saída para almoço</option>
                        <option value="lunch_in"  {{ old('field') === 'lunch_in'  ? 'selected' : '' }}>Retorno do almoço</option>
                        <option value="clock_out" {{ old('field') === 'clock_out' ? 'selected' : '' }}>Saída final</option>
                    </select>
                </div>
                @error('field')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            {{-- Preview antes → depois --}}
            <div x-show="field" x-transition class="p-3 bg-slate-50 rounded-xl border border-slate-200">
                <p class="text-xs text-gray-500 mb-2 font-medium">Preview da alteração</p>
                <div class="flex items-center gap-3 justify-center">
                    <div class="text-center px-4 py-2 bg-white rounded-lg border border-gray-200">
                        <p class="text-xs text-gray-400">Atual</p>
                        <p class="text-lg font-bold font-mono text-gray-700" x-text="currentTime"></p>
                    </div>
                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                    <div class="text-center px-4 py-2 bg-indigo-50 rounded-lg border border-indigo-200">
                        <p class="text-xs text-indigo-500">Novo</p>
                        <p class="text-lg font-bold font-mono text-indigo-600">??:??</p>
                    </div>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Horário correto</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <input type="datetime-local" name="requested_time"
                           value="{{ old('requested_time', $workLog->work_date->format('Y-m-d') . 'T08:00') }}"
                           class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-gray-300 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition @error('requested_time') border-red-400 @enderror">
                </div>
                @error('requested_time')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Justificativa</label>
                <div class="relative">
                    <textarea name="reason" rows="3"
                              placeholder="Descreva o motivo da correção (mínimo 10 caracteres)..."
                              class="w-full rounded-xl border border-gray-300 px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition @error('reason') border-red-400 @enderror">{{ old('reason') }}</textarea>
                </div>
                @error('reason')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            <div class="flex justify-end gap-3 pt-4 border-t border-gray-100">
                <a href="{{ route('worklogs.show', $workLog) }}"
                   class="px-5 py-2.5 text-sm text-gray-600 hover:text-gray-800 font-medium transition">Cancelar</a>
                <button type="submit" :disabled="loading"
                    class="px-6 py-2.5 bg-gradient-to-r from-indigo-600 to-violet-600 text-white text-sm font-semibold rounded-xl hover:from-indigo-700 hover:to-violet-700 transition shadow-lg shadow-indigo-500/25 disabled:opacity-70 inline-flex items-center gap-2">
                    <svg x-show="loading" class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                    Enviar Solicitação
                </button>
            </div>
        </form>
    </div>
</div>
@endsection