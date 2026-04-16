<?php

use App\Http\Controllers\ClockAdjustmentController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\WorkLogController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn() => redirect()->route('dashboard'));

Route::middleware(['auth', 'verified'])->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('dashboard');

    Route::resource('employees', EmployeeController::class)
        ->middleware('role:gestor');

    Route::get('/worklogs', [WorkLogController::class, 'index'])
        ->name('worklogs.index');

    Route::get('/worklogs/{workLog}', [WorkLogController::class, 'show'])
        ->name('worklogs.show');

    Route::post('/punch', [WorkLogController::class, 'punch'])
        ->name('punch')
        ->middleware('role:employee');

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