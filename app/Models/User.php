<?php

/**
 * Model: User — Representa um usuário autenticável do sistema.
 *
 * Cada usuário possui um 'role' que define seu nível de acesso:
 *  - 'gestor'   → Gestor de RH (acesso total: CRUD de funcionários, todos os registros)
 *  - 'employee' → Funcionário (acesso restrito: apenas seus próprios dados e ponto)
 *
 * Relacionamentos:
 *  - hasOne Employee → Dados cadastrais do funcionário (CPF, endereço, cargo, etc.)
 *
 * Tecnologias: Laravel Eloquent, Laravel Auth (Authenticatable), Notifiable trait
 *
 * @see \App\Models\Employee
 * @see \App\Http\Middleware\EnsureUserRole
 * @see \Database\Seeders\DatabaseSeeder (credenciais de demo)
 */

namespace App\Models;

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
        ];
    }

    public function employee(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(Employee::class);
    }

    public function isEmployee(): bool
    {
        return $this->role === 'employee';
    }

    public function isGestor(): bool
    {
        return $this->role === 'gestor';
    }
}