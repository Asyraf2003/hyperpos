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
    ) {
    }

    /**
     * @param array<int, WorkItem> $rows
     * @return array<string, array<string, int|string>>
     */
    public function build(string $noteId, array $rows): array
    {
        $orderedRows = $this->sortRowsByLineNo($rows);

        $componentPaymentTotals = $this->groupPaymentAllocations($noteId);
        $componentRefundTotals = $this->groupRefundAllocations($noteId);

        if ($componentPaymentTotals !== [] || $componentRefundTotals !== []) {
            return $this->buildFromComponentAllocations(
                $orderedRows,
                $componentPaymentTotals,
                $componentRefundTotals,
            );
        }

        return $this->buildFromLegacyTotals($noteId, $orderedRows);
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
     * @param array<int, WorkItem> $rows
     * @param array<string, int> $paymentTotals
     * @param array<string, int> $refundTotals
     * @return array<string, array<string, int|string>>
     */
    private function buildFromComponentAllocations(array $rows, array $paymentTotals, array $refundTotals): array
    {
        $summary = [];

        foreach ($rows as $item) {
            $workItemId = $item->id();
            $subtotal = $item->subtotalRupiah()->amount();
            $allocated = $paymentTotals[$workItemId] ?? 0;
            $refunded = $refundTotals[$workItemId] ?? 0;
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
     * @return array<string, array<string, int|string>>
     */
    private function buildFromLegacyTotals(string $noteId, array $rows): array
    {
        $totalAllocated = $this->legacyPayments->getTotalAllocatedAmountByNoteId($noteId);
        $totalAllocated->ensureNotNegative('Total alokasi pada note tidak boleh negatif.');

        $totalRefunded = $this->legacyRefunds->getTotalRefundedAmountByNoteId($noteId);
        $totalRefunded->ensureNotNegative('Total refund pada note tidak boleh negatif.');

        $allocatedRemainder = $totalAllocated->amount();
        $refundedRemainder = $totalRefunded->amount();
        $summary = [];

        foreach ($rows as $item) {
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
     * @return array<string, int>
     */
    private function groupPaymentAllocations(string $noteId): array
    {
        $totals = [];

        foreach ($this->componentPayments->listByNoteId($noteId) as $allocation) {
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

        foreach ($this->componentRefunds->listByNoteId($noteId) as $allocation) {
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
