<?php

declare(strict_types=1);

namespace App\Application\Note\Services\CurrentRevision;

use App\Application\Note\Services\NoteOperationalSettlementLabelResolver;
use App\Core\Note\Revision\NoteRevisionLineSnapshot;

final class CurrentRevisionComponentSettlementSummaryBuilder
{
    public function __construct(
        private readonly NoteOperationalSettlementLabelResolver $labels,
    ) {
    }

    /**
     * @param list<NoteRevisionLineSnapshot> $lines
     * @param array<string, int> $paymentTotals
     * @param array<string, int> $refundTotals
     * @return array<string, array<string, int|string>>
     */
    public function build(array $lines, array $paymentTotals, array $refundTotals): array
    {
        $summary = [];

        foreach ($lines as $line) {
            $key = $line->workItemRootId() ?? $line->id();
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
}
