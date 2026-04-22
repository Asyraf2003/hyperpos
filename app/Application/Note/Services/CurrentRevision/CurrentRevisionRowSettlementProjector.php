<?php

declare(strict_types=1);

namespace App\Application\Note\Services\CurrentRevision;

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
        private readonly NoteOperationalSettlementLabelResolver $labels,
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
            return $this->buildComponentSummary($lines, $paymentTotals, $refundTotals);
        }

        return $this->buildLegacySummary(
            $lines,
            $this->legacyPayments->getTotalAllocatedAmountByNoteId($noteId)->amount(),
            $this->legacyRefunds->getTotalRefundedAmountByNoteId($noteId)->amount(),
        );
    }

    /**
     * @param list<NoteRevisionLineSnapshot> $lines
     * @param array<string, int> $paymentTotals
     * @param array<string, int> $refundTotals
     * @return array<string, array<string, int|string>>
     */
    private function buildComponentSummary(array $lines, array $paymentTotals, array $refundTotals): array
    {
        $summary = [];

        foreach ($lines as $line) {
            $key = $this->rowKey($line);
            $subtotal = $line->subtotalRupiah();
            $allocated = $paymentTotals[$key] ?? 0;
            $refunded = $refundTotals[$key] ?? 0;
            $netPaid = max($allocated - $refunded, 0);

            $summary[$key] = [
                'allocated_rupiah' => $allocated,
                'refunded_rupiah' => $refunded,
                'net_paid_rupiah' => $netPaid,
                'outstanding_rupiah' => max($subtotal - $netPaid, 0),
                'settlement_label' => $this->labels->resolve($subtotal, $netPaid),
            ];
        }

        return $summary;
    }

    /**
     * @param list<NoteRevisionLineSnapshot> $lines
     * @return array<string, array<string, int|string>>
     */
    private function buildLegacySummary(array $lines, int $totalAllocated, int $totalRefunded): array
    {
        $allocatedRemainder = $totalAllocated;
        $refundedRemainder = $totalRefunded;
        $summary = [];

        foreach ($lines as $line) {
            $subtotal = $line->subtotalRupiah();
            $allocated = min(max($allocatedRemainder, 0), $subtotal);
            $allocatedRemainder -= $allocated;

            $refunded = min(max($refundedRemainder, 0), $allocated);
            $refundedRemainder -= $refunded;

            $netPaid = max($allocated - $refunded, 0);

            $summary[$this->rowKey($line)] = [
                'allocated_rupiah' => $allocated,
                'refunded_rupiah' => $refunded,
                'net_paid_rupiah' => $netPaid,
                'outstanding_rupiah' => max($subtotal - $netPaid, 0),
                'settlement_label' => $this->labels->resolve($subtotal, $netPaid),
            ];
        }

        return $summary;
    }

    private function rowKey(NoteRevisionLineSnapshot $line): string
    {
        return $line->workItemRootId() ?? $line->id();
    }
}
