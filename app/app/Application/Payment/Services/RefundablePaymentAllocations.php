<?php

declare(strict_types=1);

namespace App\Application\Payment\Services;

use App\Core\Payment\PaymentComponentAllocation\PaymentComponentAllocation;
use App\Ports\Out\Payment\PaymentComponentAllocationReaderPort;

final class RefundablePaymentAllocations
{
    /**
     * @param list<string> $selectedRowIds
     * @return list<PaymentComponentAllocation>
     */
    public static function forPayment(
        PaymentComponentAllocationReaderPort $reader,
        string $customerPaymentId,
        string $noteId,
        array $selectedRowIds = [],
    ): array {
        $selectedIds = self::normalizeSelectedRowIds($selectedRowIds);

        $allocations = array_filter(
            $reader->listByNoteId($noteId),
            static function (PaymentComponentAllocation $allocation) use ($customerPaymentId, $selectedIds): bool {
                if ($allocation->customerPaymentId() !== $customerPaymentId) {
                    return false;
                }

                if ($selectedIds === []) {
                    return true;
                }

                return in_array($allocation->workItemId(), $selectedIds, true);
            },
        );

        usort(
            $allocations,
            static function (PaymentComponentAllocation $left, PaymentComponentAllocation $right): int {
                return $right->allocationPriority() <=> $left->allocationPriority();
            },
        );

        return $allocations;
    }

    /**
     * @param list<string> $selectedRowIds
     * @return list<string>
     */
    private static function normalizeSelectedRowIds(array $selectedRowIds): array
    {
        $normalized = [];

        foreach ($selectedRowIds as $id) {
            $trimmed = trim($id);

            if ($trimmed !== '') {
                $normalized[] = $trimmed;
            }
        }

        return array_values(array_unique($normalized));
    }
}
