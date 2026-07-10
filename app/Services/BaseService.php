<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Auth;

abstract class BaseService
{
    /**
     * Get the currently authenticated user.
     */
    protected function user(): User
    {
        return Auth::user();
    }

    /**
     * Current user ID.
     */
    protected function userId(): int
    {
        return $this->user()->id;
    }

    /**
     * Current user's province ID.
     */
    protected function provinceId(): ?int
    {
        return $this->user()->province_id;
    }

    /**
     * Current user's province name.
     */
    protected function provinceName(): ?string
    {
        return $this->user()->provinceName();
    }

    /**
     * Role helpers.
     */
    protected function isSupply(): bool
    {
        return $this->user()->isSupply();
    }

    protected function isTssd(): bool
    {
        return $this->user()->isTssd();
    }

    protected function isProvincial(): bool
    {
        return $this->user()->isProvincial();
    }

    protected function isAccounting(): bool
    {
        return $this->user()->isAccounting();
    }

    /**
     * Abort if user is not TSSD.
     */
    protected function requireTssd(): void
    {
        abort_unless($this->isTssd(), 403);
    }

    /**
     * Abort if user is not Supply.
     */
    protected function requireSupply(): void
    {
        abort_unless($this->isSupply(), 403);
    }

    /**
     * Abort if user is not Provincial Office.
     */
    protected function requireProvincial(): void
    {
        abort_unless($this->isProvincial(), 403);
    }

    /**
     * Abort if user is not Accounting.
     */
    protected function requireAccounting(): void
    {
        abort_unless($this->isAccounting(), 403);
    }
}