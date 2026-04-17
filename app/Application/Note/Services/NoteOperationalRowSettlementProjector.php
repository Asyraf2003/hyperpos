<?php

declare(strict_types=1);

namespace App\Application\Note\Services;

use App\Core\Note\WorkItem\WorkItem;
use App\Ports\Out\Payment\PaymentComponentAllocationReaderPort;
use App\Ports\Out\Payment\RefundComponentAllocationReaderPort;

final class NoteOperationalRowSettlementProjector
{
    public function __construct(
        private readonly PaymentComponentAllocationReaderPort $payments,
        private readonly RefundComponentAllocationReaderPort $refunds,
    ) {
    }

    /**
     * @param array<int, WorkItem> $rows
     * @return array<string, array<string, int|string>>
     */
    public function build(string $noteId, array $rows): array
    {
        $orderedRows = $this->sortRowsByLineNo($rows);
        $payments = $this->groupPaymentAllocations($noteId);
        $refunds = $this->groupRefundAllocations($noteId);
        $summary = [];

        foreach ($orderedRows as $item) {
            $workItemId = $item->id();
            $subtotal = $item->subtotalRupiah()->amount();
            $allocated = $payments[$workItemId] ?? 0;
            $refunded = $refunds[$workItemId] ?? 0;
            $netPaid = max($allocated - $refunded, 0);
            $outstanding = max($subtotal - $netPaid, 0);

            $summary[$workItemId] = [
                'allocated_rupiah' => $allocated,
                'refunded_rupiah' => $refunded,
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

    /**
     * @return array<string, int>
     */
    private function groupPaymentAllocations(string $noteId): array
    {
        $totals = [];

        foreach ($this->payments->listByNoteId($noteId) as $allocation) {
            $workItemId = $allocation->workItemId();
            $totals[$workItemId] = ($totals[$workItemId] ?? 0) + $allocation->allocatedAmountRupiah()->amount();
        }

        return $totals;
    }

    /**
     * @return array<string, int>
     */
    private function groupRefundAllocations(string $noteId): array
    {
        $totals = [];

        foreach ($this->refunds->listByNoteId($noteId) as $allocation) {
            $workItemId = $allocation->workItemId();
            $totals[$workItemId] = ($totals[$workItemId] ?? 0) + $allocation->refundedAmountRupiah()->amount();
        }

        return $totals;
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
