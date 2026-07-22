<?php

namespace App\Policies;

use App\Models\CallOff;
use App\Models\User;

class CallOffPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isSupply() || $user->isTssd() || $user->isAccounting();
    }

    public function view(User $user, CallOff $callOff): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $user->isSupply();
    }

    public function update(User $user, CallOff $callOff): bool
    {
        return $user->isSupply();
    }

    public function approve(User $user, CallOff $callOff): bool
    {
        return $user->isSupply();
    }

    public function print(User $user, CallOff $callOff): bool
    {
        return $user->isSupply() || $user->isTssd() || $user->isAccounting();
    }

    public function delete(User $user, CallOff $callOff): bool
    {
        return $user->isSupply() && ! $callOff->isApproved();
    }
}
