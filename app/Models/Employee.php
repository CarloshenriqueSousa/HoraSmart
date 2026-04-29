<?php

/**
 * Model: Employee — Dados cadastrais de um funcionário.
 *
 * Separado do User por design: User cuida apenas de autenticação (email/senha/role),
 * enquanto Employee armazena dados de RH (CPF, endereço, cargo, data de admissão).
 *
 * Usa SoftDeletes para manter histórico auditável — dados de ponto são documentos
 * legais (CLT) e não devem ser removidos permanentemente.
 *
 * Relacionamentos:
 *  - belongsTo User     → Cada funcionário tem exatamente um usuário
 *  - hasMany  WorkLog   → Registros de ponto do funcionário
 *  - hasOne   todayLog  → Atalho para o registro de ponto do dia atual
 *
 * @see \App\Models\User
 * @see \App\Models\WorkLog
 * @see \App\Http\Controllers\EmployeeController
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Employee extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'cpf',
        'address',
        'position',
        'employee_type',
        'shift',
        'daily_workload',
        'overtime_tolerance',
        'hired_at',
    ];

    protected function casts(): array
    {
        return [
            'hired_at'           => 'date',
            'daily_workload'     => 'integer',
            'overtime_tolerance' => 'integer',
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