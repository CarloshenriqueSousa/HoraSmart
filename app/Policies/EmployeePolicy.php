<?php

/**
 * Policy: EmployeePolicy — Autorização para operações com funcionários.
 *
 * Determina o que cada tipo de usuário pode fazer com Employee:
 *  - viewAny/create/update/delete: Apenas gestores
 *  - view: Gestores veem qualquer um; funcionário vê apenas seu próprio perfil
 *
 * Complementa o middleware 'role:gestor' nas rotas — dupla camada de segurança.
 * Usado nos controllers via $this->authorize('método', Employee::class).
 *
 * Tecnologias: Laravel Policy, Autorização baseada em modelo
 *
 * @see \App\Http\Controllers\EmployeeController
 */

namespace App\Policies;

use App\Models\Employee;
use App\Models\User;

class EmployeePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isGestor();
    }

    public function view(User $user, Employee $employee): bool
    {
        return $user->isGestor() || $user->employee?->id === $employee->id;
    }

    public function create(User $user): bool
    {
        return $user->isGestor();
    }

    public function update(User $user, Employee $employee): bool
    {
        return $user->isGestor();
    }

    public function delete(User $user, Employee $employee): bool
    {
        return $user->isGestor();
    }
}