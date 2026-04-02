<?php

declare(strict_types=1);

namespace App\Application\Payment\Services;

use App\Ports\Out\Payment\PaymentComponentAllocationReaderPort;

final class RefundablePaymentAllocations
{
    public static function forPayment(
        PaymentComponentAllocationReaderPort $reader,
        string $customerPaymentId,
        string $noteId,
    ): array {
        $allocations = array_filter(
            $reader->listByNoteId($noteId),
            static fn ($allocation): bool => $allocation->customerPaymentId() === $customerPaymentId,
        );

        usort($allocations, static function ($left, $right): int {
            return $right->allocationPriority() <=> $left->allocationPriority();
        });

        return $allocations;
    }
}
