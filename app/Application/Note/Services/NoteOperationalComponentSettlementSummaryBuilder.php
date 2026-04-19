<?php

declare(strict_types=1);

namespace App\Application\Note\Services;

use App\Core\Note\WorkItem\WorkItem;

final class NoteOperationalComponentSettlementSummaryBuilder
{
    public function __construct(
        private readonly NoteOperationalSettlementLabelResolver $labels,
    ) {
    }

    /**
     * @param array<int, WorkItem> $rows
     * @param array<string, int> $paymentTotals
     * @param array<string, int> $refundTotals
     * @return array<string, array<string, int|string>>
     */
    public function build(array $rows, array $paymentTotals, array $refundTotals): array
    {
        $summary = [];

        foreach ($rows as $item) {
            $workItemId = $item->id();
            $subtotal = $item->subtotalRupiah()->amount();
            $allocated = $paymentTotals[$workItemId] ?? 0;
            $refunded = $refundTotals[$workItemId] ?? 0;
            $netPaid = max($allocated - $refunded, 0);

            $summary[$workItemId] = [
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
