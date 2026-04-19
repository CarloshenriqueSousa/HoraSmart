@extends('layouts.app')
@section('title', 'Meu Perfil')

@section('content')
<div class="max-w-7xl mx-auto space-y-6">

    <div>
        <h1 class="text-2xl font-bold text-gray-900 mt-2">Meu Perfil</h1>
        <p class="text-gray-500 text-sm mt-1">Gerencie suas credenciais e acesse contatos importantes da empresa.</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- Coluna Esquerda: Configurações de Conta (Breeze) -->
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 sm:p-8">
                <div class="max-w-xl">
                    @include('profile.partials.update-profile-information-form')
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 sm:p-8">
                <div class="max-w-xl">
                    @include('profile.partials.update-password-form')
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 sm:p-8">
                <div class="max-w-xl">
                    @include('profile.partials.delete-user-form')
                </div>
            </div>
        </div>

        <!-- Coluna Direita: Contatos Corporativos -->
        <div class="space-y-6">
            <div class="bg-gradient-to-br from-indigo-50 to-white rounded-2xl shadow-sm border border-indigo-100 p-6 sm:p-8">
                <h3 class="text-lg font-bold text-gray-900 mb-4 flex items-center gap-2">
                    <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                    Contatos Superiores
                </h3>
                
                <div class="space-y-5">
                    @if(auth()->user()->isGestor())
                        <!-- Contato P/ Gestores -->
                        <div class="border-l-2 border-indigo-500 pl-3">
                            <p class="text-xs font-semibold text-indigo-600 uppercase tracking-wider mb-1">Diretoria Executiva</p>
                            <p class="text-sm font-medium text-gray-900">Dr. Roberto Almeida</p>
                            <p class="text-xs text-gray-500">roberto.ceo@empresa.com</p>
                            <p class="text-xs text-gray-500">Ramal: 1001</p>
                        </div>
                    @else
                        <!-- Contato P/ Funcionários -->
                        <div class="border-l-2 border-violet-500 pl-3">
                            <p class="text-xs font-semibold text-violet-600 uppercase tracking-wider mb-1">Seu Gestor Direto</p>
                            <p class="text-sm font-medium text-gray-900">Ana Beatriz Gomes (RH)</p>
                            <p class="text-xs text-gray-500">ana.rh@empresa.com</p>
                            <p class="text-xs text-gray-500">WhatsApp: (11) 98888-7777</p>
                        </div>
                        <div class="border-l-2 border-slate-300 pl-3 mt-4">
                            <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1">Encarregado do Setor</p>
                            <p class="text-sm font-medium text-gray-900">Carlos Henrique Souza</p>
                            <p class="text-xs text-gray-500">carlos.ti@empresa.com</p>
                            <p class="text-xs text-gray-500">Ramal: 2045</p>
                        </div>
                    @endif
                </div>

                <div class="mt-6 pt-5 border-t border-indigo-100/50">
                    <p class="text-xs text-justify text-gray-500">
                        Qualquer divergência de ponto, férias ou solicitação de ajuste na jornada de trabalho deve ser comunicada primeiramente ao seu <strong>Encarregado de Setor</strong> e, em sequência, aprovada pelo Gestor logado no HoraSmart via sistema de chamados.
                    </p>
                </div>
            </div>
            
            <div class="bg-white rounded-2xl shadow-sm border border-emerald-100 p-6 sm:p-8 relative overflow-hidden">
                <div class="absolute -right-4 -bottom-4 text-emerald-50 opacity-50">
                    <svg class="w-32 h-32" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"/></svg>
                </div>
                <h3 class="text-sm font-bold text-emerald-800 mb-1 relative z-10">Canal de Ética & Compliance</h3>
                <p class="text-xs text-emerald-600 mb-3 relative z-10">Reporte assédio ou abusos de forma 100% anônima.</p>
                <a href="#" class="inline-flex items-center gap-1 text-xs font-semibold text-emerald-700 hover:text-emerald-900 bg-emerald-100 px-3 py-1.5 rounded-lg transition relative z-10">
                    Acessar Canal Seguro &rarr;
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
