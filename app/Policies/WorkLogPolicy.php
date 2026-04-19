<?php

/**
 * Policy: WorkLogPolicy — Autorização para operações com registros de ponto.
 *
 * Regras:
 *  - viewAny: Todos os usuários autenticados podem listar (filtrado no controller)
 *  - view: Gestores veem qualquer registro; funcionário vê apenas os seus
 *  - punch: Apenas o próprio funcionário pode registrar ponto no seu WorkLog
 *
 * Tecnologias: Laravel Policy
 *
 * @see \App\Http\Controllers\WorkLogController
 */

namespace App\Policies;

use App\Models\User;
use App\Models\WorkLog;

class WorkLogPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, WorkLog $workLog): bool
    {
        if ($user->isGestor()) {
            return true;
        }

        return $user->employee?->id === $workLog->employee_id;
    }

    public function punch(User $user, WorkLog $workLog): bool
    {
        return $user->employee?->id === $workLog->employee_id;
    }
}