<?php

namespace App\Providers;

use App\Models\CallOff;
use App\Models\DeliveryReceipt;
use App\Models\PdfTemplate;
use App\Models\ProvinceDistribution;
use App\Models\PurchaseOrder;
use App\Models\SupplyDesignation;
use App\Models\TssdDistributionBatch;
use App\Models\User;
use App\Policies\CallOffPolicy;
use App\Policies\DeliveryReceiptPolicy;
use App\Policies\PdfTemplatePolicy;
use App\Policies\ProvinceDistributionPolicy;
use App\Policies\PurchaseOrderPolicy;
use App\Policies\SupplyDesignationPolicy;
use App\Policies\TssdDistributionBatchPolicy;
use App\Policies\UserPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Gate::policy(PurchaseOrder::class, PurchaseOrderPolicy::class);
        Gate::policy(CallOff::class, CallOffPolicy::class);
        Gate::policy(TssdDistributionBatch::class, TssdDistributionBatchPolicy::class);
        Gate::policy(ProvinceDistribution::class, ProvinceDistributionPolicy::class);
        Gate::policy(DeliveryReceipt::class, DeliveryReceiptPolicy::class);
        Gate::policy(SupplyDesignation::class, SupplyDesignationPolicy::class);
        Gate::policy(PdfTemplate::class, PdfTemplatePolicy::class);
        Gate::policy(User::class, UserPolicy::class);
    }
}
