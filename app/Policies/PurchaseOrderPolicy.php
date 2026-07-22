<?php

namespace App\Policies;

use App\Models\PurchaseOrder;
use App\Models\User;

class PurchaseOrderPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isSupply() || $user->isTssd() || $user->isAccounting();
    }

    public function view(User $user, PurchaseOrder $purchaseOrder): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $user->isSupply();
    }

    public function update(User $user, PurchaseOrder $purchaseOrder): bool
    {
        return $user->isSupply();
    }

    public function delete(User $user, PurchaseOrder $purchaseOrder): bool
    {
        return $user->isSupply()
            && ! $purchaseOrder->distributionBatches()->exists();
    }
}
