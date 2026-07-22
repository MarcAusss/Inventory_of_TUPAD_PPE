<?php

namespace App\Policies;

use App\Models\TssdDistributionBatch;
use App\Models\User;

class TssdDistributionBatchPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isSupply() || $user->isTssd() || $user->isAccounting();
    }

    public function view(User $user, TssdDistributionBatch $batch): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $user->isTssd();
    }

    public function update(User $user, TssdDistributionBatch $batch): bool
    {
        return $user->isTssd() && $batch->isEditable();
    }

    public function submit(User $user, TssdDistributionBatch $batch): bool
    {
        return $user->isTssd() && $batch->status === 'Draft';
    }

    public function delete(User $user, TssdDistributionBatch $batch): bool
    {
        return $user->isTssd() && $batch->status === 'Draft';
    }
}
