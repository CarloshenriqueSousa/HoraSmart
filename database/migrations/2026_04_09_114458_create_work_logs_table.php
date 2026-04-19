<?php

/**
 * Migration: Cria a tabela 'work_logs' para registros de jornada diária.
 *
 * Cada funcionário tem no máximo UM registro por dia (constraint UNIQUE).
 * A jornada é composta por 4 timestamps nullable (preenchidos progressivamente):
 *
 *   clock_in → lunch_out → lunch_in → clock_out
 *
 * O campo 'status' é uma máquina de estados:
 *   in_progress → on_lunch → back_from_lunch → complete
 *
 * 'minutes_worked' é calculado automaticamente ao completar a jornada
 * e armazenado como inteiro (minutos) para evitar imprecisão float.
 *
 * @see \App\Models\WorkLog
 * @see \App\Services\WorkLogService
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('work_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->date('work_date');
            $table->timestamp('clock_in')->nullable();
            $table->timestamp('lunch_out')->nullable();
            $table->timestamp('lunch_in')->nullable();
            $table->timestamp('clock_out')->nullable();
            $table->integer('minutes_worked')->nullable();
            $table->enum('status', [
                'in_progress',
                'on_lunch',
                'back_from_lunch',
                'complete',
            ])->default('in_progress');
            $table->timestamps();

            $table->unique(['employee_id', 'work_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('work_logs');
    }
};
