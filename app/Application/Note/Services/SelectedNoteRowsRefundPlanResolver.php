<?php

declare(strict_types=1);

namespace App\Application\Note\Services;

use App\Application\Payment\DTO\SelectedRowsRefundPlan;
use App\Application\Shared\DTO\Result;
use App\Ports\Out\Note\NoteReaderPort;
use App\Ports\Out\Payment\PaymentComponentAllocationReaderPort;
use App\Ports\Out\Payment\RefundComponentAllocationReaderPort;

final class SelectedNoteRowsRefundPlanResolver
{
    public function __construct(
        private readonly NoteReaderPort $notes,
        private readonly NoteOperationalRowSettlementProjector $settlements,
        private readonly PaymentComponentAllocationReaderPort $allocations,
        private readonly RefundComponentAllocationReaderPort $refunds,
        private readonly SelectedRowsRefundBucketsBuilder $buckets,
        private readonly SelectedNoteRowsRefundEligibilityGuard $eligibility,
    ) {
    }

    /**
     * @param list<string> $selectedRowIds
     */
    public function resolve(string $noteId, array $selectedRowIds): Result
    {
        $note = $this->notes->getById(trim($noteId));

        if ($note === null) {
            return Result::failure('Nota tidak ditemukan.', ['refund' => ['REFUND_INVALID_TARGET']]);
        }

        $selectedIds = array_values(array_unique(array_filter(
            $selectedRowIds,
            static fn (string $id): bool => trim($id) !== '',
        )));

        if ($selectedIds === []) {
            return Result::failure('Minimal satu line wajib dipilih.', ['refund' => ['INVALID_SELECTED_ROWS']]);
        }

        $itemsById = [];
        foreach ($note->workItems() as $item) {
            $itemsById[$item->id()] = $item;
        }

        $settlements = $this->settlements->build($note->id(), $note->workItems());
        $ineligible = $this->eligibility->validate($selectedIds, $itemsById, $settlements);

        if ($ineligible instanceof Result) {
            return $ineligible;
        }

        $paymentBuckets = $this->buckets->build(
            $selectedIds,
            $this->allocations->listByNoteId($note->id()),
            $this->refunds->listByNoteId($note->id()),
        );

        $paidRowIds = [];
        foreach ($paymentBuckets as $bucket) {
            $paidRowIds = array_merge($paidRowIds, $bucket->rowIds());
        }

        $unpaidRowIds = array_values(array_diff($selectedIds, array_values(array_unique($paidRowIds))));
        $plan = SelectedRowsRefundPlan::create($note->id(), $selectedIds, $unpaidRowIds, $paymentBuckets);

        return Result::success([
            'plan' => $plan,
            'plan_array' => $plan->toArray(),
        ]);
    }
}
