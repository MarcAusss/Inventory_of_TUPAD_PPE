<?php

namespace App\Policies;

use App\Models\CallOff;
use App\Models\User;

class CallOffPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isTssd()
            || $user->isSupply()
            || $user->isAccounting();
    }

    public function view(
        User $user,
        CallOff $callOff
    ): bool {
        return $user->isTssd()
            || $user->isSupply()
            || $user->isAccounting();
    }

    public function create(User $user): bool
    {
        return $user->isSupply();
    }

    public function update(
        User $user,
        CallOff $callOff
    ): bool {
        return false;
    }

    public function delete(
        User $user,
        CallOff $callOff
    ): bool {
        return false;
    }

    public function restore(
        User $user,
        CallOff $callOff
    ): bool {
        return false;
    }

    public function forceDelete(
        User $user,
        CallOff $callOff
    ): bool {
        return false;
    }
}
