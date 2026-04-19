<?php

/**
 * Model: Employee — Dados cadastrais de um funcionário.
 *
 * Separado do User por design: User cuida apenas de autenticação (email/senha/role),
 * enquanto Employee armazena dados de RH (CPF, endereço, cargo, data de admissão).
 * Isso permite que o sistema de autenticação permaneça limpo e desacoplado.
 *
 * Relacionamentos:
 *  - belongsTo User     → Cada funcionário tem exatamente um usuário
 *  - hasMany  WorkLog   → Registros de ponto do funcionário
 *  - hasOne   todayLog  → Atalho para o registro de ponto do dia atual
 *
 * Tecnologias: Laravel Eloquent, Carbon (cast de 'hired_at' para date)
 *
 * @see \App\Models\User
 * @see \App\Models\WorkLog
 * @see \App\Http\Controllers\EmployeeController
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'cpf',
        'address',
        'position',
        'hired_at',
    ];

    protected function casts(): array
    {
        return [
            'hired_at' => 'date',
        ];
    }

    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function workLogs(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(WorkLog::class);
    }

    public function todayLog(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(WorkLog::class)
            ->whereDate('work_date', today());
    }
}