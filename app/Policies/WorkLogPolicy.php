<?php

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