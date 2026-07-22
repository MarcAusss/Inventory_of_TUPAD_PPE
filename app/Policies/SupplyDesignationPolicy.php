<?php

namespace App\Policies;

use App\Models\SupplyDesignation;
use App\Models\User;

class SupplyDesignationPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isTssd()
            || $user->isAccounting()
            || ($user->isProvincial() && $user->hasProvince());
    }

    public function view(User $user, SupplyDesignation $designation): bool
    {
        if ($user->isTssd() || $user->isAccounting()) {
            return true;
        }

        return $this->ownsDesignation($user, $designation);
    }

    public function create(User $user): bool
    {
        return $user->isProvincial() && $user->hasProvince();
    }

    public function update(User $user, SupplyDesignation $designation): bool
    {
        return $this->ownsDesignation($user, $designation)
            && $designation->status !== 'Submitted';
    }

    public function delete(User $user, SupplyDesignation $designation): bool
    {
        return $this->ownsDesignation($user, $designation)
            && $designation->status !== 'Submitted';
    }

    private function ownsDesignation(User $user, SupplyDesignation $designation): bool
    {
        return $user->isProvincial()
            && $user->hasProvince()
            && (int) $designation->province_id === (int) $user->province_id;
    }
}
