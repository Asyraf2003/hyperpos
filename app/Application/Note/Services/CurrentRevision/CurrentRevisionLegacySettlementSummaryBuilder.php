<?php

declare(strict_types=1);

namespace App\Application\Note\Services\CurrentRevision;

use App\Application\Note\Services\NoteOperationalSettlementLabelResolver;
use App\Core\Note\Revision\NoteRevisionLineSnapshot;

final class CurrentRevisionLegacySettlementSummaryBuilder
{
    public function __construct(
        private readonly NoteOperationalSettlementLabelResolver $labels,
    ) {
    }

    /**
     * @param list<NoteRevisionLineSnapshot> $lines
     * @return array<string, array<string, int|string>>
     */
    public function build(array $lines, int $totalAllocated, int $totalRefunded): array
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

            $summary[$line->workItemRootId() ?? $line->id()] = [
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
