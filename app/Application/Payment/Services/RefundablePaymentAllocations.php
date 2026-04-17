<?php

declare(strict_types=1);

namespace App\Application\Payment\Services;

use App\Ports\Out\Payment\PaymentComponentAllocationReaderPort;

final class RefundablePaymentAllocations
{
    /**
     * @param list<string> $selectedRowIds
     * @return array<int, mixed>
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
            static function ($allocation) use ($customerPaymentId, $selectedIds): bool {
                if ($allocation->customerPaymentId() !== $customerPaymentId) {
                    return false;
                }

                if ($selectedIds === []) {
                    return true;
                }

                return in_array($allocation->workItemId(), $selectedIds, true);
            },
        );

        usort($allocations, static function ($left, $right): int {
            return $right->allocationPriority() <=> $left->allocationPriority();
        });

        return array_values($allocations);
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
