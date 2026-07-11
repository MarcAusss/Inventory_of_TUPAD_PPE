<?php

namespace App\Services;

use App\Models\DeliveryReceipt;
use App\Models\User;
use App\Models\WorkflowNotification;
use Illuminate\Support\Collection;

class WorkflowNotificationService
{
    /**
     * Notify all TSSD users when a province submits a Delivery Receipt.
     *
     * @return Collection<int, WorkflowNotification>
     */
    public function notifyTssdOfReceiving(
        DeliveryReceipt $receipt
    ): Collection {
        $receipt->loadMissing([
            'province',
            'items.item',
            'provinceDistribution.distributionBatch.callOff',
            'provinceDistribution.distributionBatch.purchaseOrder',
        ]);

        $callOff = $receipt
            ->provinceDistribution
            ?->distributionBatch
            ?->callOff;

        $hasDiscrepancy = $receipt
            ->items
            ->contains(
                fn ($item): bool => (int) $item->assigned_quantity
                    !== (int) $item->received_quantity
            );

        $provinceName = $receipt
            ->province
            ?->name
            ?? 'Provincial Office';

        $callOffNumber = $callOff
            ?->call_off_number
            ?? 'No Call-Off Number';

        $type = $hasDiscrepancy
            ? 'Receiving Discrepancy'
            : 'Delivery Received';

        $title = $hasDiscrepancy
            ? "{$provinceName} reported a receiving discrepancy"
            : "{$provinceName} confirmed PPE delivery";

        $message = $this->buildMessage(
            $receipt,
            $provinceName,
            $callOffNumber,
            $hasDiscrepancy
        );

        $tssdUsers = User::query()
            ->whereHas(
                'role',
                fn ($query) => $query->where(
                    'name',
                    'TSSD Unit'
                )
            )
            ->get();

        return $tssdUsers->map(
            function (User $user) use (
                $receipt,
                $callOff,
                $type,
                $title,
                $message
            ): WorkflowNotification {
                return WorkflowNotification::create([
                    'recipient_user_id' => $user->id,
                    'province_id' => $receipt->province_id,
                    'call_off_id' => $callOff?->id,
                    'delivery_receipt_id' => $receipt->id,
                    'type' => $type,
                    'title' => $title,
                    'message' => $message,
                    'reference_type' => DeliveryReceipt::class,
                    'reference_id' => $receipt->id,
                    'status' => 'Unread',
                    'read_at' => null,
                    'resolved_at' => null,
                ]);
            }
        );
    }

    private function buildMessage(
        DeliveryReceipt $receipt,
        string $provinceName,
        string $callOffNumber,
        bool $hasDiscrepancy
    ): string {
        $message = implode(' ', [
            "{$provinceName} submitted Delivery Receipt",
            "{$receipt->dr_number}",
            "for Call-Off {$callOffNumber}.",
        ]);

        if ($hasDiscrepancy) {
            $message .= ' One or more received quantities differ from the assigned quantities.';
        } else {
            $message .= ' All assigned quantities were received completely.';
        }

        if ($receipt->remarks) {
            $message .= ' Remarks: '.trim($receipt->remarks);
        }

        return $message;
    }
}
