<?php

/**
 * Migration: Adiciona soft deletes às tabelas employees e work_logs.
 *
 * Dados de ponto são documentos legais (CLT) e não devem ser removidos
 * permanentemente. SoftDeletes permite "excluir" mantendo o registro no banco
 * para auditoria e compliance.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::table('work_logs', function (Blueprint $table) {
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('work_logs', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
};
