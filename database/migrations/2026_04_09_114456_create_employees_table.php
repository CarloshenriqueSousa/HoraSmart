<?php

/**
 * Migration: Cria a tabela 'employees' para dados cadastrais de funcionários.
 *
 * Cada employee está vinculado a um User (1:1) via foreign key cascadeOnDelete,
 * ou seja, se o User for removido, o Employee também é removido automaticamente.
 *
 * Campos:
 *  - cpf (14 chars, unique) → Formato 000.000.000-00, validação no FormRequest
 *  - address (text)         → Endereço completo
 *  - position (string)      → Cargo do funcionário
 *  - hired_at (date)        → Data de admissão
 *
 * @see \App\Models\Employee
 * @see \App\Http\Requests\StoreEmployeeRequest
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('cpf', 14)->unique();
            $table->text('address');
            $table->string('position');
            $table->date('hired_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
