{{--
    View: employees/edit.blade.php — Formulário de edição de funcionário.
    Layout: layouts/app.blade.php
--}}
@extends('layouts.app')
@section('title', 'Editar Funcionário')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">

    <div>
        <a href="{{ route('employees.index') }}" class="inline-flex items-center gap-1 text-sm text-indigo-600 hover:text-indigo-800 font-medium transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            Voltar
        </a>
        <h1 class="text-2xl font-bold text-gray-900 mt-2">Editar: {{ $employee->user->name }}</h1>
        <p class="text-gray-500 text-sm mt-1">Atualize os dados do funcionário</p>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
        <form method="POST" action="{{ route('employees.update', $employee) }}" class="space-y-5" x-data="{ loading: false }" @submit="loading = true">
            @csrf @method('PUT')

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Nome completo</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                        </div>
                        <input type="text" name="name" value="{{ old('name', $employee->user->name) }}"
                               class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-gray-300 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition @error('name') border-red-400 @enderror">
                    </div>
                    @error('name')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">E-mail</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"/></svg>
                        </div>
                        <input type="email" name="email" value="{{ old('email', $employee->user->email) }}"
                               class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-gray-300 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition @error('email') border-red-400 @enderror">
                    </div>
                    @error('email')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">CPF</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2"/></svg>
                        </div>
                        <input type="text" name="cpf" value="{{ old('cpf', $employee->cpf) }}"
                               class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-gray-300 text-sm font-mono focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition @error('cpf') border-red-400 @enderror">
                    </div>
                    @error('cpf')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Cargo</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                        </div>
                        <input type="text" name="position" value="{{ old('position', $employee->position) }}"
                               class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-gray-300 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition @error('position') border-red-400 @enderror">
                    </div>
                    @error('position')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Data de admissão</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        </div>
                        <input type="date" name="hired_at" value="{{ old('hired_at', $employee->hired_at->format('Y-m-d')) }}" max="{{ today()->format('Y-m-d') }}"
                               class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-gray-300 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition @error('hired_at') border-red-400 @enderror">
                    </div>
                    @error('hired_at')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                <div class="sm:col-span-2 grid grid-cols-1 sm:grid-cols-4 gap-5 p-4 bg-gray-50 rounded-xl border border-gray-100">
                    <div class="sm:col-span-4">
                        <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Configurações Base de Jornada</h4>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Tipo Contratual</label>
                        <select name="employee_type" class="w-full px-4 py-2.5 rounded-xl border border-gray-300 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition @error('employee_type') border-red-400 @enderror">
                            <option value="CLT" {{ old('employee_type', $employee->employee_type) == 'CLT' ? 'selected' : '' }}>CLT (Padrão)</option>
                            <option value="Estagiário" {{ old('employee_type', $employee->employee_type) == 'Estagiário' ? 'selected' : '' }}>Estagiário</option>
                            <option value="Trainee" {{ old('employee_type', $employee->employee_type) == 'Trainee' ? 'selected' : '' }}>Trainee</option>
                        </select>
                        @error('employee_type')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Turno</label>
                        <select name="shift" class="w-full px-4 py-2.5 rounded-xl border border-gray-300 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition @error('shift') border-red-400 @enderror">
                            <option value="morning" {{ old('shift', $employee->shift) == 'morning' ? 'selected' : '' }}>Manhã</option>
                            <option value="afternoon" {{ old('shift', $employee->shift) == 'afternoon' ? 'selected' : '' }}>Tarde</option>
                            <option value="night" {{ old('shift', $employee->shift) == 'night' ? 'selected' : '' }}>Noite</option>
                        </select>
                        @error('shift')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Horas P/ Dia</label>
                        <div class="relative">
                            <input type="number" name="daily_workload" value="{{ old('daily_workload', $employee->daily_workload) }}" min="60" max="480"
                                   class="w-full pl-4 pr-10 py-2.5 rounded-xl border border-gray-300 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition @error('daily_workload') border-red-400 @enderror">
                            <div class="absolute inset-y-0 right-0 pr-3.5 flex items-center pointer-events-none">
                                <span class="text-gray-400 text-xs">min</span>
                            </div>
                        </div>
                        <p class="text-[10px] text-gray-400 mt-1">Ex: 8h = 480 min</p>
                        @error('daily_workload')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Tolerância (Atraso)</label>
                        <div class="relative">
                            <input type="number" name="overtime_tolerance" value="{{ old('overtime_tolerance', $employee->overtime_tolerance) }}" min="0" max="10"
                                   class="w-full pl-4 pr-10 py-2.5 rounded-xl border border-gray-300 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition @error('overtime_tolerance') border-red-400 @enderror">
                            <div class="absolute inset-y-0 right-0 pr-3.5 flex items-center pointer-events-none">
                                <span class="text-gray-400 text-xs">min</span>
                            </div>
                        </div>
                        <p class="text-[10px] text-gray-400 mt-1">Máximo legal: 10m</p>
                        @error('overtime_tolerance')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div class="sm:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Endereço</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        </div>
                        <input type="text" name="address" value="{{ old('address', $employee->address) }}"
                               class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-gray-300 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition @error('address') border-red-400 @enderror">
                    </div>
                    @error('address')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
            </div>

            <div class="flex justify-end gap-3 pt-4 border-t border-gray-100">
                <a href="{{ route('employees.index') }}" class="px-5 py-2.5 text-sm text-gray-600 hover:text-gray-800 font-medium transition">Cancelar</a>
                <button type="submit" :disabled="loading"
                    class="px-6 py-2.5 bg-gradient-to-r from-indigo-600 to-violet-600 text-white text-sm font-semibold rounded-xl hover:from-indigo-700 hover:to-violet-700 transition shadow-lg shadow-indigo-500/25 disabled:opacity-70 inline-flex items-center gap-2">
                    <svg x-show="loading" class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                    Salvar Alterações
                </button>
            </div>
        </form>
    </div>
</div>
@endsection