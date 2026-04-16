<?php

namespace App\Policies;

use App\Models\ClockAdjustment;
use App\Models\User;

class ClockAdjustmentPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, ClockAdjustment $adjustment): bool
    {
        if ($user->isGestor()) {
            return true;
        }

        return $user->employee?->id === $adjustment->workLog->employee_id;
    }

    public function create(User $user): bool
    {
        return $user->isEmployee();
    }

    public function review(User $user): bool
    {
        return $user->isGestor();
    }
}