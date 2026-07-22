<?php

namespace App\Policies;

use App\Models\DeliveryReceipt;
use App\Models\User;

class DeliveryReceiptPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isSupply()
            || $user->isTssd()
            || $user->isAccounting()
            || ($user->isProvincial() && $user->hasProvince());
    }

    public function view(User $user, DeliveryReceipt $receipt): bool
    {
        if ($user->isSupply() || $user->isTssd() || $user->isAccounting()) {
            return true;
        }

        return $this->ownsReceipt($user, $receipt);
    }

    public function create(User $user): bool
    {
        return $user->isProvincial() && $user->hasProvince();
    }

    public function update(User $user, DeliveryReceipt $receipt): bool
    {
        return $this->ownsReceipt($user, $receipt)
            && $receipt->status !== 'Received';
    }

    public function delete(User $user, DeliveryReceipt $receipt): bool
    {
        return $this->ownsReceipt($user, $receipt)
            && $receipt->status !== 'Received';
    }

    private function ownsReceipt(User $user, DeliveryReceipt $receipt): bool
    {
        if (! $user->isProvincial() || ! $user->hasProvince()) {
            return false;
        }

        $provinceId = $receipt->province_id
            ?? $receipt->provinceDistribution?->province_id;

        return (int) $provinceId === (int) $user->province_id;
    }
}
