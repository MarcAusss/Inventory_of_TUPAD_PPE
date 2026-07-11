<?php

namespace App\Services;

use App\Models\CallOff;
use App\Models\TssdDistributionBatch;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CallOffService extends BaseService
{
    /**
     * Assign one Call-Off Number to one complete TSSD distribution batch.
     *
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): CallOff
    {
        $this->requireTssd();

        return DB::transaction(function () use ($data): CallOff {
            $batch = TssdDistributionBatch::query()
                ->with([
                    'purchaseOrder',
                    'provinceDistributions.items',
                ])
                ->lockForUpdate()
                ->findOrFail(
                    $data['tssd_distribution_batch_id']
                );

            $this->validateBatchForCallOff($batch);

            $callOff = CallOff::create([
                'tssd_distribution_batch_id' => $batch->id,
                'purchase_order_id' => $batch->purchase_order_id,
                'call_off_number' => $data['call_off_number'],
                'call_off_date' => null,
                'assigned_by' => $this->userId(),
                'assigned_at' => $data['assigned_at'],
                'approved_by' => null,
                'approved_at' => null,
                'approval_document' => null,
                'remarks' => $data['remarks'] ?? null,
                'status' => 'Pending',
            ]);

            $batch->update([
                'status' => 'Pending Approval',
            ]);

            foreach ($batch->provinceDistributions as $allocation) {
                $allocation->update([
                    'status' => 'Pending',
                ]);
            }

            return $callOff->load([
                'distributionBatch.purchaseOrder.supplier',
                'distributionBatch.provinceDistributions.province',
                'distributionBatch.provinceDistributions.items.item',
                'assignedBy',
            ]);
        });
    }

    /**
     * Approve or reject a pending Call-Off.
     *
     * @param  array<string, mixed>  $data
     */
    public function review(
        CallOff $callOff,
        array $data,
        ?UploadedFile $approvalDocument = null
    ): CallOff {
        $this->requireSupply();

        return DB::transaction(function () use (
            $callOff,
            $data,
            $approvalDocument
        ): CallOff {
            $callOff = CallOff::query()
                ->with([
                    'distributionBatch.provinceDistributions',
                ])
                ->lockForUpdate()
                ->findOrFail($callOff->id);

            if ($callOff->status !== 'Pending') {
                throw ValidationException::withMessages([
                    'decision' => "Only pending Call-Offs can be reviewed. Current status: {$callOff->status}.",
                ]);
            }

            $decision = $data['decision'];

            $documentPath = $callOff->approval_document;

            if ($approvalDocument) {
                $documentPath = $approvalDocument->store(
                    'call-off-approvals',
                    'public'
                );
            }

            if ($decision === 'Approved') {
                $callOff->update([
                    'call_off_date' => $data['call_off_date'],
                    'approved_by' => $this->userId(),
                    'approved_at' => now(),
                    'approval_document' => $documentPath,
                    'remarks' => $this->mergeRemarks(
                        $callOff->remarks,
                        $data['remarks'] ?? null,
                        'Supply Approval'
                    ),
                    'status' => 'Approved',
                ]);

                $callOff->distributionBatch?->update([
                    'status' => 'Approved',
                ]);

                foreach (
                    $callOff->distributionBatch?->provinceDistributions
                        ?? collect() as $allocation
                ) {
                    $allocation->update([
                        'status' => 'For Delivery',
                    ]);
                }
            }

            if ($decision === 'Rejected') {
                $callOff->update([
                    'call_off_date' => null,
                    'approved_by' => $this->userId(),
                    'approved_at' => now(),
                    'approval_document' => $documentPath,
                    'remarks' => $this->mergeRemarks(
                        $callOff->remarks,
                        $data['remarks'] ?? null,
                        'Supply Rejection'
                    ),
                    'status' => 'Rejected',
                ]);

                $callOff->distributionBatch?->update([
                    'status' => 'Submitted',
                ]);

                foreach (
                    $callOff->distributionBatch?->provinceDistributions
                        ?? collect() as $allocation
                ) {
                    $allocation->update([
                        'status' => 'Pending',
                    ]);
                }
            }

            return $callOff->fresh([
                'distributionBatch.purchaseOrder.supplier',
                'distributionBatch.provinceDistributions.province',
                'distributionBatch.provinceDistributions.items.item',
                'assignedBy',
                'approvedBy',
            ]);
        });
    }

    /**
     * Cancel a pending Call-Off while preserving the audit record.
     */
    public function cancel(CallOff $callOff): CallOff
    {
        $this->requireTssd();

        return DB::transaction(function () use ($callOff): CallOff {
            $callOff = CallOff::query()
                ->with([
                    'distributionBatch.provinceDistributions',
                ])
                ->lockForUpdate()
                ->findOrFail($callOff->id);

            if ($callOff->status !== 'Pending') {
                throw ValidationException::withMessages([
                    'call_off' => 'Only pending Call-Offs may be cancelled.',
                ]);
            }

            $callOff->update([
                'status' => 'Cancelled',
            ]);

            $callOff->distributionBatch?->update([
                'status' => 'Submitted',
            ]);

            foreach (
                $callOff->distributionBatch?->provinceDistributions
                    ?? collect() as $allocation
            ) {
                $allocation->update([
                    'status' => 'Pending',
                ]);
            }

            return $callOff;
        });
    }

    /**
     * Validate that a distribution batch may receive a Call-Off.
     */
    private function validateBatchForCallOff(
        TssdDistributionBatch $batch
    ): void {
        if ($batch->callOff()->exists()) {
            throw ValidationException::withMessages([
                'tssd_distribution_batch_id' => 'This distribution batch already has a Call-Off Number.',
            ]);
        }

        if ($batch->provinceDistributions->isEmpty()) {
            throw ValidationException::withMessages([
                'tssd_distribution_batch_id' => 'The distribution batch has no provincial allocations.',
            ]);
        }

        $hasEmptyAllocation = $batch
            ->provinceDistributions
            ->contains(
                fn ($allocation): bool => $allocation->items->isEmpty()
            );

        if ($hasEmptyAllocation) {
            throw ValidationException::withMessages([
                'tssd_distribution_batch_id' => 'Every provincial allocation must contain at least one PPE item.',
            ]);
        }

        if (! in_array($batch->status, [
            'Draft',
            'Submitted',
        ], true)) {
            throw ValidationException::withMessages([
                'tssd_distribution_batch_id' => "A Call-Off cannot be assigned while the batch status is {$batch->status}.",
            ]);
        }
    }

    /**
     * Preserve existing remarks while recording the Supply decision.
     */
    private function mergeRemarks(
        ?string $existingRemarks,
        ?string $newRemarks,
        string $heading
    ): ?string {
        $newRemarks = trim((string) $newRemarks);

        if ($newRemarks === '') {
            return $existingRemarks;
        }

        $entry = sprintf(
            "[%s - %s]\n%s",
            $heading,
            now()->format('Y-m-d H:i:s'),
            $newRemarks
        );

        if (! $existingRemarks) {
            return $entry;
        }

        return $existingRemarks."\n\n".$entry;
    }
}
