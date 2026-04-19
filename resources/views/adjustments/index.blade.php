{{--
    View: adjustments/index.blade.php — Lista de solicitações de ajuste de ponto.

    Gestor: vê todos, pode aprovar/rejeitar via modal. Preview "antes → depois".
    Funcionário: vê apenas seus pedidos.

    Features:
     - Filtro por status (Alpine.js)
     - Status badges com ícones
     - Modal de revisão (gestor) com preview antes/depois

    Layout: layouts/app.blade.php
--}}
@extends('layouts.app')
@section('title', 'Ajustes de Ponto')

@section('content')
<div class="space-y-6" x-data="adjustmentManager()">

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Ajustes de Ponto</h1>
            <p class="text-gray-500 text-sm mt-0.5">
                {{ auth()->user()->isGestor() ? 'Todas as solicitações de ajuste' : 'Suas solicitações de correção' }}
            </p>
        </div>
    </div>

    {{-- Filtros --}}
    <div class="flex items-center gap-1 bg-gray-100 rounded-xl p-1 w-fit">
        @php
            $filters = ['all' => 'Todos', 'pending' => 'Pendentes', 'approved' => 'Aprovados', 'rejected' => 'Rejeitados'];
        @endphp
        @foreach($filters as $key => $label)
        <button @click="statusFilter = '{{ $key }}'"
                :class="statusFilter === '{{ $key }}' ? 'bg-white shadow-sm text-gray-900' : 'text-gray-500 hover:text-gray-700'"
                class="px-3 py-1.5 text-xs font-medium rounded-lg transition-all">
            {{ $label }}
        </button>
        @endforeach
    </div>

    {{-- Lista --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        @if($adjustments->isEmpty())
            <div class="px-6 py-16 text-center">
                <svg class="w-12 h-12 text-gray-200 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
                <p class="text-gray-400 text-sm">Nenhuma solicitação de ajuste.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-100 text-sm">
                    <thead class="bg-gray-50/80 text-xs font-medium text-gray-500 uppercase">
                        <tr>
                            <th class="px-6 py-3 text-left">Solicitado por</th>
                            <th class="px-6 py-3 text-left">Data</th>
                            <th class="px-6 py-3 text-left">Campo</th>
                            <th class="px-6 py-3 text-left">Horário Solicitado</th>
                            <th class="px-6 py-3 text-left">Motivo</th>
                            <th class="px-6 py-3 text-left">Status</th>
                            @if(auth()->user()->isGestor())
                                <th class="px-6 py-3 text-right">Ação</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @foreach($adjustments as $adj)
                        <tr class="hover:bg-gray-50/50 transition"
                            x-show="statusFilter === 'all' || statusFilter === '{{ $adj->status }}'"
                            x-transition>
                            <td class="px-6 py-3">
                                <div>
                                    <p class="font-medium text-gray-800">{{ $adj->requester->name }}</p>
                                    <p class="text-xs text-gray-400">{{ $adj->created_at->format('d/m H:i') }}</p>
                                </div>
                            </td>
                            <td class="px-6 py-3 text-gray-600 text-xs">{{ $adj->workLog->work_date->format('d/m/Y') }}</td>
                            <td class="px-6 py-3">
                                @php $fieldLabels = ['clock_in' => 'Entrada', 'lunch_out' => 'Saída Almoço', 'lunch_in' => 'Retorno', 'clock_out' => 'Saída Final']; @endphp
                                <span class="inline-flex px-2 py-0.5 bg-slate-100 text-slate-700 rounded-lg text-xs font-medium">{{ $fieldLabels[$adj->field] ?? $adj->field }}</span>
                            </td>
                            <td class="px-6 py-3 font-mono text-sm font-semibold text-indigo-600">{{ \Carbon\Carbon::parse($adj->requested_time)->format('H:i') }}</td>
                            <td class="px-6 py-3 text-gray-600 text-xs max-w-[200px] truncate" title="{{ $adj->reason }}">{{ $adj->reason }}</td>
                            <td class="px-6 py-3">
                                @php
                                    $statusConfig = [
                                        'pending' => ['label' => 'Pendente', 'class' => 'bg-amber-100 text-amber-700', 'icon' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z'],
                                        'approved' => ['label' => 'Aprovado', 'class' => 'bg-emerald-100 text-emerald-700', 'icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'],
                                        'rejected' => ['label' => 'Rejeitado', 'class' => 'bg-red-100 text-red-700', 'icon' => 'M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z'],
                                    ];
                                    $sc = $statusConfig[$adj->status] ?? $statusConfig['pending'];
                                @endphp
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-lg text-xs font-medium {{ $sc['class'] }}">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $sc['icon'] }}"/></svg>
                                    {{ $sc['label'] }}
                                </span>
                            </td>
                            @if(auth()->user()->isGestor())
                                <td class="px-6 py-3 text-right">
                                    @if($adj->status === 'pending')
                                        <button @click="openReview({{ $adj->id }}, '{{ $adj->requester->name }}', '{{ $fieldLabels[$adj->field] ?? $adj->field }}', '{{ $adj->workLog->{$adj->field}?->format('H:i') ?? '--:--' }}', '{{ \Carbon\Carbon::parse($adj->requested_time)->format('H:i') }}', '{{ addslashes($adj->reason) }}')"
                                                class="px-3 py-1.5 text-xs font-semibold text-indigo-600 bg-indigo-50 hover:bg-indigo-100 rounded-lg transition">
                                            Revisar
                                        </button>
                                    @endif
                                </td>
                            @endif
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="px-6 py-4 border-t border-gray-100">
                {{ $adjustments->links() }}
            </div>
        @endif
    </div>

    {{-- Modal de Revisão (Gestor) --}}
    @if(auth()->user()->isGestor())
    <div x-show="showModal" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 flex items-center justify-center px-4" style="display:none;">
        <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="showModal = false"></div>
        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-md p-6 animate-scale-in" @click.outside="showModal = false">
            <h3 class="text-lg font-bold text-gray-900 mb-4">Revisar Ajuste</h3>

            {{-- Preview antes → depois --}}
            <div class="mb-5 p-4 bg-slate-50 rounded-xl border border-slate-200">
                <p class="text-xs text-gray-500 font-medium mb-3 uppercase tracking-wide">Alteração Solicitada</p>
                <p class="text-sm text-gray-700 mb-2"><strong x-text="reviewName"></strong> · <span class="text-gray-500" x-text="reviewField"></span></p>
                <div class="flex items-center gap-3">
                    <div class="flex-1 text-center py-2 bg-white rounded-lg border border-gray-200">
                        <p class="text-xs text-gray-400">Antes</p>
                        <p class="text-lg font-bold font-mono text-gray-800" x-text="reviewBefore"></p>
                    </div>
                    <svg class="w-5 h-5 text-gray-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                    <div class="flex-1 text-center py-2 bg-indigo-50 rounded-lg border border-indigo-200">
                        <p class="text-xs text-indigo-500">Depois</p>
                        <p class="text-lg font-bold font-mono text-indigo-600" x-text="reviewAfter"></p>
                    </div>
                </div>
                <p class="text-xs text-gray-500 mt-3 italic">"<span x-text="reviewReason"></span>"</p>
            </div>

            <form :action="'/adjustments/' + reviewId + '/review'" method="POST" class="space-y-4">
                @csrf @method('PATCH')

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Comentário (opcional)</label>
                    <textarea name="reviewer_comment" rows="2" placeholder="Comentário para o funcionário..."
                              class="w-full rounded-xl border border-gray-300 px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition"></textarea>
                </div>

                <div class="flex gap-3">
                    <button type="submit" name="status" value="approved"
                            class="flex-1 py-2.5 bg-emerald-600 text-white text-sm font-semibold rounded-xl hover:bg-emerald-700 transition inline-flex items-center justify-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        Aprovar
                    </button>
                    <button type="submit" name="status" value="rejected"
                            class="flex-1 py-2.5 bg-red-600 text-white text-sm font-semibold rounded-xl hover:bg-red-700 transition inline-flex items-center justify-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        Rejeitar
                    </button>
                </div>
            </form>

            <button @click="showModal = false" class="absolute top-4 right-4 text-gray-400 hover:text-gray-600 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
    </div>
    @endif
</div>

<script>
function adjustmentManager() {
    return {
        statusFilter: 'all',
        showModal: false,
        reviewId: null,
        reviewName: '',
        reviewField: '',
        reviewBefore: '',
        reviewAfter: '',
        reviewReason: '',

        openReview(id, name, field, before, after, reason) {
            this.reviewId = id;
            this.reviewName = name;
            this.reviewField = field;
            this.reviewBefore = before;
            this.reviewAfter = after;
            this.reviewReason = reason;
            this.showModal = true;
        },
    };
}
</script>
@endsection