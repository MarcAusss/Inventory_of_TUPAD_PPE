<?php

namespace App\Policies;

use App\Models\CallOff;
use App\Models\User;

class CallOffPolicy
{
    /**
     * TSSD, Supply, and Accounting may view the Call-Off list.
     */
    public function viewAny(User $user): bool
    {
        return $user->isTssd()
            || $user->isSupply()
            || $user->isAccounting();
    }

    /**
     * TSSD, Supply, and Accounting may view any Call-Off.
     *
     * Provincial Office access will later be handled through its own
     * province-restricted allocation routes.
     */
    public function view(User $user, CallOff $callOff): bool
    {
        return $user->isTssd()
            || $user->isSupply()
            || $user->isAccounting();
    }

    /**
     * Only TSSD may assign a Call-Off Number.
     */
    public function create(User $user): bool
    {
        return $user->isTssd();
    }

    /**
     * Only TSSD may edit a pending or rejected Call-Off.
     */
    public function update(User $user, CallOff $callOff): bool
    {
        return $user->isTssd()
            && $callOff->isEditable();
    }

    /**
     * Only TSSD may cancel a pending Call-Off.
     *
     * Permanent deletion is not allowed because Call-Offs form part of the
     * audit trail.
     */
    public function delete(User $user, CallOff $callOff): bool
    {
        return $user->isTssd()
            && $callOff->status === 'Pending';
    }

    public function restore(User $user, CallOff $callOff): bool
    {
        return false;
    }

    public function forceDelete(User $user, CallOff $callOff): bool
    {
        return false;
    }
}
