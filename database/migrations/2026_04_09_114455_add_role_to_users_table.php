<?php

/**
 * Migration: Adiciona coluna 'role' à tabela users.
 *
 * Define dois perfis de acesso no sistema:
 *  - 'gestor'   → Gestor de RH (pode cadastrar funcionários, ver todos os registros)
 *  - 'employee' → Funcionário (registra ponto, vê apenas seus dados)
 *
 * O campo utiliza enum para garantir integridade a nível de banco de dados,
 * com default 'employee' para que novos usuários tenham o perfil mais restrito.
 *
 * @see \App\Models\User::isGestor()
 * @see \App\Models\User::isEmployee()
 * @see \App\Http\Middleware\EnsureUserRole
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['gestor', 'employee'])->default('employee')->after('password');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('role');
        });
    }
};
