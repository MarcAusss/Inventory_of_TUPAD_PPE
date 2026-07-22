<?php

namespace App\Policies;

use App\Models\ProvinceDistribution;
use App\Models\User;

class ProvinceDistributionPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isSupply()
            || $user->isTssd()
            || $user->isAccounting()
            || ($user->isProvincial() && $user->hasProvince());
    }

    public function view(User $user, ProvinceDistribution $distribution): bool
    {
        if ($user->isSupply() || $user->isTssd() || $user->isAccounting()) {
            return true;
        }

        return $user->isProvincial()
            && $user->hasProvince()
            && (int) $distribution->province_id === (int) $user->province_id;
    }

    public function create(User $user): bool
    {
        return $user->isTssd();
    }

    public function update(User $user, ProvinceDistribution $distribution): bool
    {
        return $user->isTssd();
    }

    public function delete(User $user, ProvinceDistribution $distribution): bool
    {
        return $user->isTssd() && $distribution->status === 'Draft';
    }
}
