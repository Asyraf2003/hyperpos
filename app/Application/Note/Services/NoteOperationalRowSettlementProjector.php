<?php

declare(strict_types=1);

namespace App\Application\Note\Services;

use App\Core\Note\WorkItem\WorkItem;
use App\Ports\Out\Payment\CustomerRefundReaderPort;
use App\Ports\Out\Payment\PaymentAllocationReaderPort;

final class NoteOperationalRowSettlementProjector
{
    public function __construct(
        private readonly PaymentAllocationReaderPort $allocations,
        private readonly CustomerRefundReaderPort $refunds,
    ) {
    }

    /**
     * @param array<int, WorkItem> $rows
     * @return array<string, array<string, int|string>>
     */
    public function build(string $noteId, array $rows): array
    {
        $orderedRows = $this->sortRowsByLineNo($rows);

        $totalAllocated = $this->allocations->getTotalAllocatedAmountByNoteId($noteId);
        $totalAllocated->ensureNotNegative('Total alokasi pada note tidak boleh negatif.');

        $totalRefunded = $this->refunds->getTotalRefundedAmountByNoteId($noteId);
        $totalRefunded->ensureNotNegative('Total refund pada note tidak boleh negatif.');

        $allocatedRemainder = $totalAllocated->amount();
        $refundedRemainder = $totalRefunded->amount();

        $summary = [];

        foreach ($orderedRows as $item) {
            $subtotal = $item->subtotalRupiah()->amount();

            $projectedAllocated = min(max($allocatedRemainder, 0), $subtotal);
            $allocatedRemainder -= $projectedAllocated;

            $projectedRefunded = min(max($refundedRemainder, 0), $projectedAllocated);
            $refundedRemainder -= $projectedRefunded;

            $netPaid = max($projectedAllocated - $projectedRefunded, 0);
            $outstanding = max($subtotal - $netPaid, 0);

            $summary[$item->id()] = [
                'allocated_rupiah' => $projectedAllocated,
                'refunded_rupiah' => $projectedRefunded,
                'net_paid_rupiah' => $netPaid,
                'outstanding_rupiah' => $outstanding,
                'settlement_label' => $this->label($subtotal, $netPaid),
            ];
        }

        return $summary;
    }

    /**
     * @param array<int, WorkItem> $rows
     * @return array<int, WorkItem>
     */
    private function sortRowsByLineNo(array $rows): array
    {
        usort(
            $rows,
            static fn (WorkItem $left, WorkItem $right): int => $left->lineNo() <=> $right->lineNo()
        );

        return $rows;
    }

    private function label(int $subtotal, int $netPaid): string
    {
        if ($subtotal <= 0 || $netPaid <= 0) {
            return 'hutang';
        }

        if ($netPaid >= $subtotal) {
            return 'lunas';
        }

        return 'dp';
    }
}
