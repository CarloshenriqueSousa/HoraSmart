<?php

/**
 * Routes: web.php — Rotas HTTP do HoraSmart.
 *
 * Organização:
 *  - Rota raiz '/' redireciona para o dashboard
 *  - Grupo autenticado (auth + verified): todas as rotas do sistema
 *  - Middleware 'role:gestor' nas rotas de gestão (employees, aprovação de ajustes, exportações)
 *  - Middleware 'role:employee' na rota de punch (apenas funcionários batem ponto)
 *
 * Rotas principais:
 *  GET  /dashboard                → Painel (redireciona por role)
 *  CRUD /employees                → Gestão de funcionários (gestor only)
 *  GET  /employees/export/csv     → Exportar lista CSV (gestor only)
 *  GET  /worklogs                 → Lista de registros de ponto
 *  GET  /worklogs/export/csv      → Exportar registros CSV (gestor only)
 *  GET  /worklogs/export/pdf      → Exportar relatório PDF (gestor only)
 *  GET  /worklogs/{id}/edit       → Editar registro (gestor only)
 *  PUT  /worklogs/{id}            → Atualizar registro (gestor only)
 *  POST /punch                    → Registrar batida (AJAX, employee only)
 *  CRUD /adjustments              → Solicitações de ajuste
 *  PATCH /adjustments/{id}/review → Aprovar/rejeitar ajuste (gestor only)
 *
 * Tecnologias: Laravel Router, Middleware Groups, Resource Routes
 *
 * @see \App\Http\Middleware\EnsureUserRole
 * @see routes/auth.php (rotas de autenticação do Breeze)
 */

use App\Http\Controllers\ClockAdjustmentController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\WorkLogController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn() => redirect()->route('dashboard'));

Route::middleware(['auth', 'verified'])->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('dashboard');

    Route::get('/profile', [ProfileController::class, 'edit'])
        ->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])
        ->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])
        ->name('profile.destroy');

    // CRUD Funcionários (gestor only)
    Route::resource('employees', EmployeeController::class)
        ->middleware('role:gestor');

    // Exportação CSV de funcionários (gestor only)
    Route::get('/employees-export/csv', [EmployeeController::class, 'export'])
        ->name('employees.export.csv')
        ->middleware('role:gestor');

    // Registros de ponto
    Route::get('/worklogs', [WorkLogController::class, 'index'])
        ->name('worklogs.index');

    Route::get('/worklogs/export/csv', [WorkLogController::class, 'export'])
        ->name('worklogs.export.csv')
        ->middleware('role:gestor');

    Route::get('/worklogs/export/pdf', [WorkLogController::class, 'exportPdf'])
        ->name('worklogs.export.pdf')
        ->middleware('role:gestor');

    Route::get('/worklogs/{workLog}', [WorkLogController::class, 'show'])
        ->name('worklogs.show');

    Route::get('/worklogs/{workLog}/edit', [WorkLogController::class, 'edit'])
        ->name('worklogs.edit')
        ->middleware('role:gestor');

    Route::put('/worklogs/{workLog}', [WorkLogController::class, 'update'])
        ->name('worklogs.update')
        ->middleware('role:gestor');

    // Punch (AJAX) — rate limit: 5 por minuto para evitar batidas duplicadas
    Route::post('/punch', [WorkLogController::class, 'punch'])
        ->name('punch')
        ->middleware(['role:employee', 'throttle:5,1']);

    // Ajustes de ponto
    Route::get('/adjustments', [ClockAdjustmentController::class, 'index'])
        ->name('adjustments.index');

    Route::get('/adjustments/create', [ClockAdjustmentController::class, 'create'])
        ->name('adjustments.create');

    Route::post('/adjustments', [ClockAdjustmentController::class, 'store'])
        ->name('adjustments.store');

    Route::patch('/adjustments/{adjustment}/review', [ClockAdjustmentController::class, 'review'])
        ->name('adjustments.review')
        ->middleware('role:gestor');
});

require __DIR__.'/auth.php';