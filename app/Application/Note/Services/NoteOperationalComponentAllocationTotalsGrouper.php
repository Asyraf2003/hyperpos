<?php

declare(strict_types=1);

namespace App\Application\Note\Services;

use App\Core\Payment\PaymentComponentAllocation\PaymentComponentAllocation;
use App\Core\Payment\RefundComponentAllocation\RefundComponentAllocation;

final class NoteOperationalComponentAllocationTotalsGrouper
{
    /**
     * @param list<PaymentComponentAllocation> $allocations
     * @return array<string, int>
     */
    public function paymentTotals(array $allocations): array
    {
        $totals = [];

        foreach ($allocations as $allocation) {
            $workItemId = $allocation->workItemId();
            $totals[$workItemId] = ($totals[$workItemId] ?? 0) + $allocation->allocatedAmountRupiah()->amount();
        }

        return $totals;
    }

    /**
     * @param list<RefundComponentAllocation> $allocations
     * @return array<string, int>
     */
    public function refundTotals(array $allocations): array
    {
        $totals = [];

        foreach ($allocations as $allocation) {
            $workItemId = $allocation->workItemId();
            $totals[$workItemId] = ($totals[$workItemId] ?? 0) + $allocation->refundedAmountRupiah()->amount();
        }

        return $totals;
    }
}
