@extends('layouts.app')
@section('title', 'Meu Painel')

@section('content')
<div class="space-y-6">

    <div>
        <h1 class="text-2xl font-bold text-gray-900">Olá, {{ auth()->user()->name }}</h1>
        <p class="text-gray-500 text-sm mt-1">{{ now()->format('l, d \d\e F \d\e Y') }}</p>
    </div>

    @if(!$employee)
        <div class="bg-yellow-50 border border-yellow-200 text-yellow-800 rounded-lg p-4 text-sm">
            Seu perfil de funcionário ainda não foi configurado. Entre em contato com o gestor.
        </div>
    @else

        {{-- Card de bater ponto --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6"
             x-data="pontoCard()" x-init="init()">

            <div class="flex items-center justify-between mb-4">
                <h2 class="text-base font-semibold text-gray-800">Registro de Ponto — Hoje</h2>
                <span class="text-sm text-gray-400" x-text="horaAtual"></span>
            </div>

            {{-- Linha do tempo --}}
            <div class="grid grid-cols-4 gap-2 mb-6">
                @php
                    $batidas = [
                        ['label' => 'Entrada',      'value' => $todayLog?->clock_in],
                        ['label' => 'Saída almoço', 'value' => $todayLog?->lunch_out],
                        ['label' => 'Retorno',      'value' => $todayLog?->lunch_in],
                        ['label' => 'Saída final',  'value' => $todayLog?->clock_out],
                    ];
                @endphp
                @foreach($batidas as $batida)
                <div class="text-center p-3 rounded-lg {{ $batida['value'] ? 'bg-indigo-50 border border-indigo-200' : 'bg-gray-50 border border-gray-100' }}">
                    <p class="text-xs text-gray-500 mb-1">{{ $batida['label'] }}</p>
                    <p class="text-sm font-semibold {{ $batida['value'] ? 'text-indigo-700' : 'text-gray-300' }}">
                        {{ $batida['value'] ? $batida['value']->format('H:i') : '--:--' }}
                    </p>
                </div>
                @endforeach
            </div>

            {{-- Botão de bater ponto --}}
            @if(!$todayLog || !$todayLog->isComplete())
                <button @click="baterPonto()"
                    :disabled="loading"
                    class="w-full py-3 px-6 rounded-xl font-semibold text-white bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50 disabled:cursor-not-allowed transition text-sm">
                    <span x-show="!loading" x-text="labelBotao">
                        {{ $todayLog ? 'Continuar jornada' : 'Registrar Entrada' }}
                    </span>
                    <span x-show="loading" class="flex items-center justify-center gap-2">
                        <svg class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
                        </svg>
                        Registrando...
                    </span>
                </button>
            @else
                <div class="w-full py-3 px-6 rounded-xl text-center font-semibold text-green-700 bg-green-50 border border-green-200 text-sm">
                    ✓ Jornada de hoje finalizada
                </div>
            @endif

            {{-- Feedback --}}
            <div x-show="mensagem" x-transition
                 class="mt-3 text-center text-sm"
                 :class="sucesso ? 'text-green-600' : 'text-red-500'"
                 x-text="mensagem"></div>
        </div>

        {{-- Saldo de horas --}}
        <div class="grid grid-cols-2 gap-4">
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Horas esta semana</p>
                @php
                    $wh = intdiv($weekMinutes, 60);
                    $wm = $weekMinutes % 60;
                @endphp
                <p class="text-3xl font-bold text-indigo-600 mt-1">{{ sprintf('%02d:%02d', $wh, $wm) }}</p>
                <p class="text-xs text-gray-400 mt-1">Meta: 44:00</p>
            </div>
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Horas este mês</p>
                @php
                    $mh = intdiv($monthMinutes, 60);
                    $mm = $monthMinutes % 60;
                @endphp
                <p class="text-3xl font-bold text-indigo-600 mt-1">{{ sprintf('%02d:%02d', $mh, $mm) }}</p>
                <p class="text-xs text-gray-400 mt-1">{{ now()->format('F') }}</p>
            </div>
        </div>

        {{-- Histórico recente --}}
        @if($recentLogs->isNotEmpty())
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100">
                <h2 class="text-base font-semibold text-gray-800">Últimos 7 dias</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-100 text-sm">
                    <thead class="bg-gray-50 text-xs font-medium text-gray-500 uppercase">
                        <tr>
                            <th class="px-6 py-3 text-left">Data</th>
                            <th class="px-6 py-3 text-left">Entrada</th>
                            <th class="px-6 py-3 text-left">Saída</th>
                            <th class="px-6 py-3 text-left">Horas</th>
                            <th class="px-6 py-3 text-left">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @foreach($recentLogs as $log)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-3 font-medium text-gray-700">
                                {{ $log->work_date->format('d/m') }}
                            </td>
                            <td class="px-6 py-3 text-gray-600">{{ $log->clock_in?->format('H:i') ?? '—' }}</td>
                            <td class="px-6 py-3 text-gray-600">{{ $log->clock_out?->format('H:i') ?? '—' }}</td>
                            <td class="px-6 py-3 font-medium text-indigo-600">{{ $log->formatted_hours }}</td>
                            <td class="px-6 py-3">
                                @if($log->status === 'complete')
                                    <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700">Completo</span>
                                @else
                                    <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-700">Em andamento</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif
    @endif
</div>

<script>
function pontoCard() {
    return {
        loading: false,
        mensagem: '',
        sucesso: false,
        labelBotao: '{{ $todayLog ? match($todayLog->status) { "in_progress" => "Registrar Saída para Almoço", "on_lunch" => "Registrar Retorno do Almoço", "back_from_lunch" => "Registrar Saída Final", default => "Bater Ponto" } : "Registrar Entrada" }}',
        horaAtual: '',

        init() {
            this.atualizarHora();
            setInterval(() => this.atualizarHora(), 1000);
        },

        atualizarHora() {
            const now = new Date();
            this.horaAtual = now.toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
        },

        async baterPonto() {
            this.loading = true;
            this.mensagem = '';
            try {
                const res = await fetch('{{ route('punch') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                });
                const data = await res.json();
                this.sucesso = data.success;
                this.mensagem = data.message;
                if (data.success) {
                    setTimeout(() => window.location.reload(), 1200);
                }
            } catch {
                this.sucesso = false;
                this.mensagem = 'Erro ao registrar. Tente novamente.';
            } finally {
                this.loading = false;
            }
        }
    }
}
</script>
@endsection