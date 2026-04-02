<?php

declare(strict_types=1);

namespace App\Application\Note\Services;

use App\Ports\Out\Payment\PaymentComponentAllocationReaderPort;
use App\Ports\Out\Payment\RefundComponentAllocationReaderPort;

final class NoteRowSettlementSummaryBuilder
{
    public function __construct(
        private readonly PaymentComponentAllocationReaderPort $payments,
        private readonly RefundComponentAllocationReaderPort $refunds,
    ) {
    }

    /**
     * @return array<string, array<string, int|string>>
     */
    public function build(string $noteId, array $rows): array
    {
        $payments = $this->groupPaymentAllocations($noteId);
        $refunds = $this->groupRefundAllocations($noteId);
        $summary = [];

        foreach ($rows as $item) {
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
}
