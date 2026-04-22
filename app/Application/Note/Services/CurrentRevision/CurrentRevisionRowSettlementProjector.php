<?php

declare(strict_types=1);

namespace App\Application\Note\Services\CurrentRevision;

use App\Application\Note\Services\NoteOperationalComponentAllocationTotalsGrouper;
use App\Core\Note\Revision\NoteRevisionLineSnapshot;
use App\Ports\Out\Payment\CustomerRefundReaderPort;
use App\Ports\Out\Payment\PaymentAllocationReaderPort;
use App\Ports\Out\Payment\PaymentComponentAllocationReaderPort;
use App\Ports\Out\Payment\RefundComponentAllocationReaderPort;

final class CurrentRevisionRowSettlementProjector
{
    public function __construct(
        private readonly PaymentComponentAllocationReaderPort $componentPayments,
        private readonly RefundComponentAllocationReaderPort $componentRefunds,
        private readonly PaymentAllocationReaderPort $legacyPayments,
        private readonly CustomerRefundReaderPort $legacyRefunds,
        private readonly NoteOperationalComponentAllocationTotalsGrouper $totalsGrouper,
        private readonly CurrentRevisionComponentSettlementSummaryBuilder $componentSummary,
        private readonly CurrentRevisionLegacySettlementSummaryBuilder $legacySummary,
    ) {
    }

    /**
     * @param list<NoteRevisionLineSnapshot> $lines
     * @return array<string, array<string, int|string>>
     */
    public function build(string $noteId, array $lines): array
    {
        usort($lines, static fn (NoteRevisionLineSnapshot $a, NoteRevisionLineSnapshot $b): int => $a->lineNo() <=> $b->lineNo());

        $paymentTotals = $this->totalsGrouper->paymentTotals($this->componentPayments->listByNoteId($noteId));
        $refundTotals = $this->totalsGrouper->refundTotals($this->componentRefunds->listByNoteId($noteId));

        if ($paymentTotals !== [] || $refundTotals !== []) {
            return $this->componentSummary->build($lines, $paymentTotals, $refundTotals);
        }

        return $this->legacySummary->build(
            $lines,
            $this->legacyPayments->getTotalAllocatedAmountByNoteId($noteId)->amount(),
            $this->legacyRefunds->getTotalRefundedAmountByNoteId($noteId)->amount(),
        );
    }
}
