<?php

declare(strict_types=1);

namespace App\Application\Note\Services;

use App\Core\Note\WorkItem\WorkItem;

final class NoteOperationalLegacySettlementSummaryBuilder
{
    public function __construct(
        private readonly NoteOperationalSettlementLabelResolver $labels,
    ) {
    }

    /**
     * @param array<int, WorkItem> $rows
     * @return array<string, array<string, int|string>>
     */
    public function build(array $rows, int $totalAllocated, int $totalRefunded): array
    {
        $allocatedRemainder = $totalAllocated;
        $refundedRemainder = $totalRefunded;
        $summary = [];

        foreach ($rows as $item) {
            $subtotal = $item->subtotalRupiah()->amount();
            $allocated = min(max($allocatedRemainder, 0), $subtotal);
            $allocatedRemainder -= $allocated;

            $refunded = min(max($refundedRemainder, 0), $allocated);
            $refundedRemainder -= $refunded;

            $netPaid = max($allocated - $refunded, 0);

            $summary[$item->id()] = [
                'allocated_rupiah' => $allocated,
                'refunded_rupiah' => $refunded,
                'net_paid_rupiah' => $netPaid,
                'outstanding_rupiah' => max($subtotal - $netPaid, 0),
                'settlement_label' => $this->labels->resolve($subtotal, $netPaid),
            ];
        }

        return $summary;
    }
}
