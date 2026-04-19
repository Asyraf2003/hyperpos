<?php

declare(strict_types=1);

namespace App\Application\Payment\Services;

use App\Ports\Out\Payment\RefundComponentAllocationReaderPort;

final class RefundedComponentTotals
{
    /**
     * @return array<string, int>
     */
    public static function build(
        RefundComponentAllocationReaderPort $reader,
        string $customerPaymentId,
        string $noteId,
    ): array {
        $totals = [];

        foreach ($reader->listByNoteId($noteId) as $allocation) {
            if ($allocation->customerPaymentId() !== $customerPaymentId) {
                continue;
            }

            $key = ExistingPaymentComponentTotals::key($allocation->componentType(), $allocation->componentRefId());
            $totals[$key] = ($totals[$key] ?? 0) + $allocation->refundedAmountRupiah()->amount();
        }

        return $totals;
    }
}
