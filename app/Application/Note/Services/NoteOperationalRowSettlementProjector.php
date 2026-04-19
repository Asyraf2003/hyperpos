<?php

declare(strict_types=1);

namespace App\Application\Note\Services;

use App\Core\Note\WorkItem\WorkItem;
use App\Ports\Out\Payment\CustomerRefundReaderPort;
use App\Ports\Out\Payment\PaymentAllocationReaderPort;
use App\Ports\Out\Payment\PaymentComponentAllocationReaderPort;
use App\Ports\Out\Payment\RefundComponentAllocationReaderPort;

final class NoteOperationalRowSettlementProjector
{
    public function __construct(
        private readonly PaymentComponentAllocationReaderPort $componentPayments,
        private readonly RefundComponentAllocationReaderPort $componentRefunds,
        private readonly PaymentAllocationReaderPort $legacyPayments,
        private readonly CustomerRefundReaderPort $legacyRefunds,
        private readonly NoteOperationalComponentAllocationTotalsGrouper $totalsGrouper,
        private readonly NoteOperationalComponentSettlementSummaryBuilder $componentSummaryBuilder,
        private readonly NoteOperationalLegacySettlementSummaryBuilder $legacySummaryBuilder,
    ) {
    }

    /**
     * @param array<int, WorkItem> $rows
     * @return array<string, array<string, int|string>>
     */
    public function build(string $noteId, array $rows): array
    {
        usort($rows, static fn (WorkItem $left, WorkItem $right): int => $left->lineNo() <=> $right->lineNo());

        $paymentTotals = $this->totalsGrouper->paymentTotals($this->componentPayments->listByNoteId($noteId));
        $refundTotals = $this->totalsGrouper->refundTotals($this->componentRefunds->listByNoteId($noteId));

        if ($paymentTotals !== [] || $refundTotals !== []) {
            return $this->componentSummaryBuilder->build($rows, $paymentTotals, $refundTotals);
        }

        $totalAllocated = $this->legacyPayments->getTotalAllocatedAmountByNoteId($noteId);
        $totalAllocated->ensureNotNegative('Total alokasi pada note tidak boleh negatif.');

        $totalRefunded = $this->legacyRefunds->getTotalRefundedAmountByNoteId($noteId);
        $totalRefunded->ensureNotNegative('Total refund pada note tidak boleh negatif.');

        return $this->legacySummaryBuilder->build(
            $rows,
            $totalAllocated->amount(),
            $totalRefunded->amount(),
        );
    }
}
