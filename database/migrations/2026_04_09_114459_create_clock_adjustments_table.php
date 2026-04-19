<?php

/**
 * Migration: Cria a tabela 'clock_adjustments' para solicitações de ajuste de ponto.
 *
 * Quando um funcionário identifica um horário incorreto no seu registro de ponto,
 * ele pode solicitar uma correção ao gestor. Esta tabela armazena essas solicitações
 * com fluxo de aprovação.
 *
 * Colunas principais:
 *  - work_log_id    → Registro de ponto que será ajustado
 *  - requested_by   → Usuário que solicitou o ajuste (sempre um employee)
 *  - reviewed_by    → Gestor que revisou (null até ser avaliado)
 *  - field          → Campo a ser corrigido: clock_in, lunch_out, lunch_in, clock_out
 *  - requested_time → Horário correto informado pelo funcionário
 *  - reason         → Justificativa obrigatória (mín. 10 caracteres)
 *  - status         → Fluxo: pending → approved/rejected
 *
 * @see \App\Models\ClockAdjustment
 * @see \App\Http\Controllers\ClockAdjustmentController
 * @see \App\Policies\ClockAdjustmentPolicy
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clock_adjustments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('work_log_id')->constrained()->cascadeOnDelete();
            $table->foreignId('requested_by')->constrained('users')->cascadeOnDelete();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('field', ['clock_in', 'lunch_out', 'lunch_in', 'clock_out']);
            $table->timestamp('requested_time');
            $table->text('reason');
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('reviewer_comment')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clock_adjustments');
    }
};
