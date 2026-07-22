<?php

namespace App\Policies;

use App\Models\PdfTemplate;
use App\Models\User;

class PdfTemplatePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isTssd();
    }

    public function view(User $user, PdfTemplate $template): bool
    {
        return $user->isTssd();
    }

    public function create(User $user): bool
    {
        return $user->isTssd();
    }

    public function update(User $user, PdfTemplate $template): bool
    {
        return $user->isTssd();
    }

    public function delete(User $user, PdfTemplate $template): bool
    {
        return $user->isTssd();
    }
}
