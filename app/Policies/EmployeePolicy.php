<?php

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