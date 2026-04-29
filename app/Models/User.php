<?php

/**
 * Model: User — Representa um usuário autenticável do sistema.
 *
 * Cada usuário possui um 'role' (UserRole enum) que define seu nível de acesso:
 *  - Gestor   → Gestor de RH (acesso total: CRUD de funcionários, todos os registros)
 *  - Employee → Funcionário (acesso restrito: apenas seus próprios dados e ponto)
 *
 * Relacionamentos:
 *  - hasOne Employee → Dados cadastrais do funcionário (CPF, endereço, cargo, etc.)
 *
 * @see \App\Enums\UserRole
 * @see \App\Models\Employee
 * @see \App\Http\Middleware\EnsureUserRole
 */

namespace App\Models;

use App\Enums\UserRole;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
            'role'              => UserRole::class,
        ];
    }

    public function employee(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(Employee::class);
    }

    public function isEmployee(): bool
    {
        return $this->role === UserRole::Employee;
    }

    public function isGestor(): bool
    {
        return $this->role === UserRole::Gestor;
    }
}