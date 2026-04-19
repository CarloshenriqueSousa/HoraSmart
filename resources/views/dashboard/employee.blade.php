{{--
    View: dashboard/employee.blade.php — Painel pessoal do Funcionário.

    Seções:
     1. Relógio digital grande com gradiente
     2. Botão de registro de ponto via AJAX (com animação de pulse ring)
     3. Timeline visual das batidas do dia
     4. Cards de indicadores (Horas Hoje, Horas Semana, Horas Mês, Extras Mês)
     5. Histórico recente (últimos 7 dias)

    O registro de ponto é feito via fetch() sem reload da página.
    Alpine.js controla o estado do botão e atualiza a UI.

    Dados: DashboardController::employeeDashboard()

    Tecnologias: Blade, Tailwind CSS, Alpine.js, Fetch API (AJAX), CSS Progress Ring
    Layout: layouts/app.blade.php
--}}
@extends('layouts.app')
@section('title', 'Dashboard')

@section('content')
<div class="space-y-6">

    {{-- Saudação --}}
    <div>
        @php
            $hour = now()->hour;
            $greeting = $hour < 12 ? 'Bom dia' : ($hour < 18 ? 'Boa tarde' : 'Boa noite');
        @endphp
        <h1 class="text-2xl font-bold text-gray-900">
            {{ $greeting }}, {{ explode(' ', auth()->user()->name)[0] }} 👋
        </h1>
        <p class="text-gray-500 text-sm mt-0.5">
            {{ now()->translatedFormat('l, d \d\e F \d\e Y') }}
        </p>
    </div>

    @if(!$employee)
        <div class="bg-amber-50 border border-amber-200 rounded-2xl p-6 text-center">
            <svg class="w-12 h-12 text-amber-400 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/>
            </svg>
            <p class="text-amber-800 font-semibold">Perfil de funcionário não encontrado.</p>
            <p class="text-amber-600 text-sm mt-1">Procure o gestor de RH para configurar seu cadastro.</p>
        </div>
    @else

    <div x-data="punchClock()" class="space-y-6">

        {{-- Relógio + Botão de ponto --}}
        <div class="bg-gradient-to-br from-indigo-600 via-indigo-700 to-violet-700 rounded-3xl p-8 text-center shadow-xl shadow-indigo-500/20 relative overflow-hidden">
            {{-- Decorative circles --}}
            <div class="absolute -top-16 -right-16 w-48 h-48 bg-white/5 rounded-full"></div>
            <div class="absolute -bottom-12 -left-12 w-36 h-36 bg-white/5 rounded-full"></div>

            <div class="relative z-10">
                {{-- Relógio digital --}}
                <div class="mb-6">
                    <p class="text-indigo-200 text-sm font-medium mb-2 uppercase tracking-widest">Horário Atual</p>
                    <div class="text-6xl sm:text-7xl font-bold text-white tracking-tight font-mono"
                         x-text="clock" x-init="
                            setInterval(() => {
                                const now = new Date();
                                clock = now.toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
                            }, 1000);
                         ">
                        {{ now()->format('H:i:s') }}
                    </div>
                </div>

                {{-- Status atual --}}
                @if($todayLog)
                    <p class="text-indigo-200 text-sm mb-4">
                        @php
                            $statusLabels = [
                                'in_progress' => '✅ Entrada registrada — aguardando saída para almoço',
                                'on_lunch' => '🍽️ Em almoço — registre o retorno',
                                'back_from_lunch' => '🔄 De volta — registre a saída final',
                                'complete' => '🏁 Jornada finalizada — bom descanso!',
                            ];
                        @endphp
                        {{ $statusLabels[$todayLog->status] ?? '' }}
                    </p>
                @else
                    <p class="text-indigo-200 text-sm mb-4">Nenhum registro hoje — registre sua entrada</p>
                @endif

                {{-- Botão de ponto --}}
                @if(!$todayLog || $todayLog->status !== 'complete')
                    <div class="relative inline-flex items-center justify-center">
                        {{-- Ring animado --}}
                        <div class="absolute w-20 h-20 rounded-full border-2 border-white/30 animate-pulse-ring"></div>
                        <button @click="registerPunch()"
                                :disabled="loading"
                                class="relative w-20 h-20 rounded-full bg-white text-indigo-600 font-bold text-sm shadow-xl hover:scale-110 active:scale-95 transition-all duration-200 flex items-center justify-center disabled:opacity-50">
                            <svg x-show="loading" class="animate-spin w-6 h-6" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                            </svg>
                            <svg x-show="!loading" class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 8v4l3 3"/>
                            </svg>
                        </button>
                    </div>

                    @php
                        $actionLabels = [
                            null => 'Registrar Entrada',
                            'lunch_out' => 'Saída p/ Almoço',
                            'lunch_in' => 'Retorno do Almoço',
                            'clock_out' => 'Registrar Saída',
                        ];
                    @endphp
                    <p class="text-white font-semibold text-sm mt-4" x-text="actionLabel">
                        {{ $actionLabels[$todayLog?->next_action ?? null] ?? 'Registrar Entrada' }}
                    </p>
                @else
                    <div class="inline-flex items-center gap-2 bg-white/20 rounded-full px-5 py-2.5 backdrop-blur-sm">
                        <svg class="w-5 h-5 text-emerald-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span class="text-white font-semibold text-sm">Jornada Finalizada — {{ $todayLog->formatted_hours }}</span>
                    </div>
                @endif

                {{-- Feedback message --}}
                <div x-show="message" x-transition
                     class="mt-4 text-sm font-medium px-4 py-2 rounded-xl inline-block"
                     :class="success ? 'bg-emerald-500/20 text-emerald-100' : 'bg-red-500/20 text-red-100'"
                     x-text="message">
                </div>
            </div>
        </div>

        {{-- Timeline das batidas --}}
        @if($todayLog)
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
            <h2 class="text-sm font-semibold text-gray-500 uppercase tracking-wide mb-5">Batidas de Hoje</h2>
            <div class="flex items-center justify-between gap-2">
                @php
                    $steps = [
                        ['field' => 'clock_in', 'label' => 'Entrada', 'icon' => 'M11 16l-4-4m0 0l4-4m-4 4h14'],
                        ['field' => 'lunch_out', 'label' => 'Almoço', 'icon' => 'M17 16l4-4m0 0l-4-4m4 4H3'],
                        ['field' => 'lunch_in', 'label' => 'Retorno', 'icon' => 'M11 16l-4-4m0 0l4-4m-4 4h14'],
                        ['field' => 'clock_out', 'label' => 'Saída', 'icon' => 'M17 16l4-4m0 0l-4-4m4 4H3'],
                    ];
                @endphp
                @foreach($steps as $i => $step)
                    @php $time = $todayLog->{$step['field']}; @endphp
                    <div class="flex-1 text-center">
                        <div class="w-10 h-10 mx-auto rounded-xl flex items-center justify-center mb-2 transition-all
                            {{ $time ? 'bg-indigo-100 text-indigo-600' : 'bg-gray-100 text-gray-300' }}">
                            @if($time)
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            @else
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $step['icon'] }}"/></svg>
                            @endif
                        </div>
                        <p class="text-xs font-medium {{ $time ? 'text-gray-700' : 'text-gray-400' }}">{{ $step['label'] }}</p>
                        <p class="text-sm font-mono font-bold mt-0.5 {{ $time ? 'text-indigo-600' : 'text-gray-300' }}">
                            {{ $time?->format('H:i') ?? '--:--' }}
                        </p>
                    </div>
                    @if($i < 3)
                        <div class="flex-shrink-0 w-8 h-0.5 {{ $todayLog->{$steps[$i+1]['field']} ? 'bg-indigo-300' : 'bg-gray-200' }} rounded-full mt-[-16px]"></div>
                    @endif
                @endforeach
            </div>
        </div>
        @endif

        {{-- Cards de Indicadores --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 stagger-children">
            @php
                $todayMinutes = $todayLog?->minutes_worked ?? 0;
                $todayHours = sprintf('%02d:%02d', intdiv($todayMinutes, 60), $todayMinutes % 60);
                $todayPct = $todayMinutes > 0 ? min(100, round($todayMinutes / 480 * 100)) : 0;

                $weekHours = sprintf('%02d:%02d', intdiv($weekMinutes, 60), $weekMinutes % 60);
                $weekPct = $weekMinutes > 0 ? min(100, round($weekMinutes / 2400 * 100)) : 0;

                $monthHours = sprintf('%02d:%02d', intdiv($monthMinutes, 60), $monthMinutes % 60);
                $monthPct = $monthMinutes > 0 ? min(100, round($monthMinutes / 10560 * 100)) : 0;

                $overtimeFormatted = sprintf('%02d:%02d', intdiv($overtimeMonth, 60), $overtimeMonth % 60);

                $indicators = [
                    ['label' => 'Hoje', 'value' => $todayHours, 'sub' => 'de 08:00', 'pct' => $todayPct, 'color' => 'indigo'],
                    ['label' => 'Semana', 'value' => $weekHours, 'sub' => 'de 40:00', 'pct' => $weekPct, 'color' => 'violet'],
                    ['label' => 'Mês', 'value' => $monthHours, 'sub' => $workingDaysMonth . ' dias', 'pct' => $monthPct, 'color' => 'blue'],
                    ['label' => 'Extras (mês)', 'value' => $overtimeFormatted, 'sub' => 'horas extras', 'pct' => min(100, $overtimeMonth > 0 ? 100 : 0), 'color' => $overtimeMonth > 0 ? 'rose' : 'emerald'],
                ];
            @endphp

            @foreach($indicators as $ind)
            <div class="bg-white rounded-2xl border border-gray-100 p-5 shadow-sm card-hover">
                <p class="text-xs text-gray-500 font-medium uppercase tracking-wide mb-3">{{ $ind['label'] }}</p>
                <p class="text-2xl font-bold text-gray-900 font-mono">{{ $ind['value'] }}</p>
                <p class="text-xs text-gray-400 mt-0.5">{{ $ind['sub'] }}</p>
                <div class="mt-3 w-full h-1.5 bg-gray-100 rounded-full overflow-hidden">
                    <div class="h-full rounded-full bg-{{ $ind['color'] }}-500 transition-all duration-1000"
                         style="width: {{ $ind['pct'] }}%"></div>
                </div>
            </div>
            @endforeach
        </div>

        {{-- Histórico Recente --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                <div>
                    <h2 class="text-base font-semibold text-gray-800">Últimos Registros</h2>
                    <p class="text-xs text-gray-400 mt-0.5">Últimos 7 dias</p>
                </div>
                <a href="{{ route('worklogs.index') }}" class="text-sm text-indigo-600 hover:text-indigo-800 font-medium transition">Ver todos →</a>
            </div>

            @if($recentLogs->isEmpty())
                <div class="px-6 py-12 text-center">
                    <p class="text-gray-400 text-sm">Nenhum registro recente.</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-100 text-sm">
                        <thead class="bg-gray-50/80 text-xs font-medium text-gray-500 uppercase">
                            <tr>
                                <th class="px-6 py-3 text-left">Data</th>
                                <th class="px-6 py-3 text-left">Entrada</th>
                                <th class="px-6 py-3 text-left">Almoço</th>
                                <th class="px-6 py-3 text-left">Retorno</th>
                                <th class="px-6 py-3 text-left">Saída</th>
                                <th class="px-6 py-3 text-left">Total</th>
                                <th class="px-6 py-3 text-left">Extras</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            @foreach($recentLogs as $log)
                            <tr class="hover:bg-gray-50/50 transition cursor-pointer" onclick="window.location='{{ route('worklogs.show', $log) }}'">
                                <td class="px-6 py-3 font-medium text-gray-700">{{ $log->work_date->format('d/m') }}</td>
                                <td class="px-6 py-3 text-gray-600 font-mono text-xs">{{ $log->clock_in?->format('H:i') ?? '—' }}</td>
                                <td class="px-6 py-3 text-gray-600 font-mono text-xs">{{ $log->lunch_out?->format('H:i') ?? '—' }}</td>
                                <td class="px-6 py-3 text-gray-600 font-mono text-xs">{{ $log->lunch_in?->format('H:i') ?? '—' }}</td>
                                <td class="px-6 py-3 text-gray-600 font-mono text-xs">{{ $log->clock_out?->format('H:i') ?? '—' }}</td>
                                <td class="px-6 py-3 font-semibold text-indigo-600 font-mono">{{ $log->formatted_hours }}</td>
                                <td class="px-6 py-3 font-mono text-xs {{ $log->overtime_minutes > 0 ? 'text-rose-600 font-semibold' : 'text-gray-400' }}">
                                    {{ $log->formatted_overtime }}
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

    </div>
    @endif
</div>

<script>
function punchClock() {
    @php
        $actionMap = [
            'clock_in'  => 'Registrar Entrada',
            'lunch_out' => 'Saída p/ Almoço',
            'lunch_in'  => 'Retorno do Almoço',
            'clock_out' => 'Registrar Saída',
        ];
        $currentLabel = $todayLog
            ? ($actionMap[$todayLog->next_action] ?? 'Registrar Entrada')
            : 'Registrar Entrada';
    @endphp

    return {
        clock: '{{ now()->format("H:i:s") }}',
        loading: false,
        message: '',
        success: true,
        actionLabel: '{{ $currentLabel }}',

        async registerPunch() {
            this.loading = true;
            this.message = '';

            try {
                const res = await fetch('{{ route("punch") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                        'Accept': 'application/json',
                    },
                });

                const data = await res.json();
                this.success = data.success;
                this.message = data.message;

                if (data.success) {
                    setTimeout(() => location.reload(), 1500);
                }
            } catch (e) {
                this.success = false;
                this.message = 'Erro de conexão. Tente novamente.';
            } finally {
                this.loading = false;
            }
        }
    };
}
</script>
@endsection