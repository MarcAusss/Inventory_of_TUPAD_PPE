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
     * Supply Unit assigns and approves a Call-Off in one transaction.
     *
     * @param  array<string, mixed>  $data
     */
    public function assignAndApprove(
        TssdDistributionBatch $batch,
        array $data,
        UploadedFile $approvalDocument
    ): CallOff {
        $this->requireSupply();

        return DB::transaction(function () use (
            $batch,
            $data,
            $approvalDocument
        ): CallOff {
            $batch = TssdDistributionBatch::query()
                ->with([
                    'purchaseOrder',
                    'provinceDistributions.items',
                ])
                ->lockForUpdate()
                ->findOrFail($batch->id);

            $this->validateBatchForAssignment($batch);

            $documentPath = $approvalDocument->store(
                'call-off-approvals',
                'local'
            );

            $callOff = CallOff::query()->create([
                'tssd_distribution_batch_id' => $batch->id,
                'purchase_order_id' => $batch->purchase_order_id,
                'call_off_number' => $data['call_off_number'],
                'call_off_date' => $data['call_off_date'],
                'assigned_by' => $this->userId(),
                'assigned_at' => now(),
                'approved_by' => $this->userId(),
                'approved_at' => now(),
                'approval_document' => $documentPath,
                'remarks' => $data['remarks'] ?? null,
                'status' => 'Approved',
            ]);

            $batch->update([
                'status' => 'Approved',
            ]);

            foreach ($batch->provinceDistributions as $allocation) {
                $allocation->update([
                    'status' => 'Approved',
                ]);
            }

            return $callOff->fresh([
                'distributionBatch.purchaseOrder.supplier',
                'distributionBatch.creator',
                'distributionBatch.provinceDistributions.province',
                'distributionBatch.provinceDistributions.items.item',
                'assignedBy',
                'approvedBy',
            ]);
        });
    }

    private function validateBatchForAssignment(
        TssdDistributionBatch $batch
    ): void {
        if ($batch->callOff()->exists()) {
            throw ValidationException::withMessages([
                'tssd_distribution_batch_id' =>
                    'This distribution batch already has a Call-Off Number.',
            ]);
        }

        if ($batch->status !== 'Submitted') {
            throw ValidationException::withMessages([
                'tssd_distribution_batch_id' =>
                    "Only submitted distribution batches may receive a Call-Off. Current status: {$batch->status}.",
            ]);
        }

        if ($batch->provinceDistributions->isEmpty()) {
            throw ValidationException::withMessages([
                'tssd_distribution_batch_id' =>
                    'The distribution batch has no provincial allocations.',
            ]);
        }

        $hasEmptyAllocation = $batch
            ->provinceDistributions
            ->contains(
                fn ($allocation): bool =>
                    $allocation->items->isEmpty()
            );

        if ($hasEmptyAllocation) {
            throw ValidationException::withMessages([
                'tssd_distribution_batch_id' =>
                    'Every provincial allocation must contain at least one PPE item.',
            ]);
        }
    }
}
