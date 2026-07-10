<?php

namespace App\Services;

use App\Models\CallOff;
use App\Models\DeliveryReceipt;
use App\Models\Item;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Models\TSSDDistribution;

class DashboardService extends BaseService
{
    /**
     * Dashboard data for the logged-in user.
     */
    public function getDashboardData(): array
    {
        return [
            'statistics' => $this->getStatistics(),
            'charts' => $this->getCharts(),
            'recentActivities' => $this->getRecentActivities(),
        ];
    }

    /**
     * Statistics.
     */
    private function getStatistics(): array
    {
        if ($this->isSupply()) {
            return $this->supplyStatistics();
        }

        if ($this->isTssd()) {
            return $this->tssdStatistics();
        }

        if ($this->isProvincial()) {
            return $this->provincialStatistics();
        }

        if ($this->isAccounting()) {
            return $this->accountingStatistics();
        }

        return [];
    }

    /**
     * Supply Dashboard.
     */
    private function supplyStatistics(): array
    {
        return [
            'suppliers' => Supplier::count(),
            'items' => Item::count(),
            'purchase_orders' => PurchaseOrder::count(),
            'pending_purchase_orders' => PurchaseOrder::where('status', 'Pending')->count(),
        ];
    }

    /**
     * TSSD Dashboard.
     */
    private function tssdStatistics(): array
    {
        return [
            'purchase_orders' => PurchaseOrder::count(),
            'distributions' => TSSDDistribution::count(),
            'pending_calloffs' => CallOff::where('status', 'Pending')->count(),
            'approved_calloffs' => CallOff::where('status', 'Approved')->count(),
        ];
    }

    /**
     * Provincial Office Dashboard.
     */
    private function provincialStatistics(): array
    {
        return [
            'deliveries' => DeliveryReceipt::where('province_id', $this->provinceId())->count(),
            'available_items' => 0,
        ];
    }

    /**
     * Accounting Dashboard.
     */
    private function accountingStatistics(): array
    {
        return [
            'purchase_orders' => PurchaseOrder::count(),
            'distributions' => TSSDDistribution::count(),
            'calloffs' => CallOff::count(),
            'deliveries' => DeliveryReceipt::count(),
        ];
    }

    /**
     * Placeholder for dashboard charts.
     */
    private function getCharts(): array
    {
        return [];
    }

    /**
     * Placeholder for recent activities.
     */
    private function getRecentActivities(): array
    {
        return [];
    }
}