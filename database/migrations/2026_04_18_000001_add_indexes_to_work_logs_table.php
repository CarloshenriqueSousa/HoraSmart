<?php

/**
 * Migration: Adiciona índices de performance na tabela 'work_logs'.
 *
 * Sem índices, queries de dashboard (filtro por data, status, employee_id)
 * fazem full table scan — impacto significativo com volume de dados.
 *
 * Índices adicionados:
 *  - work_date          → Filtros por dia/mês/semana (dashboard, relatórios)
 *  - status             → Contagem de presentes, em almoço, completos
 *  - (employee_id, work_date) já coberto pela UNIQUE, não precisa de índice extra
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('work_logs', function (Blueprint $table) {
            $table->index('work_date');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::table('work_logs', function (Blueprint $table) {
            $table->dropIndex(['work_date']);
            $table->dropIndex(['status']);
        });
    }
};
