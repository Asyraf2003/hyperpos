<?php

declare(strict_types=1);

namespace App\Core\Payment\RefundComponentAllocation;

use App\Core\Payment\PaymentComponentAllocation\PaymentComponentType;
use App\Core\Shared\Exceptions\DomainException;
use App\Core\Shared\ValueObjects\Money;

final class RefundComponentAllocationGuard
{
    public static function assertValid(
        string $id,
        string $customerRefundId,
        string $customerPaymentId,
        string $noteId,
        string $workItemId,
        string $componentType,
        string $componentRefId,
        Money $refundedAmountRupiah,
        int $refundPriority,
    ): void {
        if (trim($id) === '') throw new DomainException('Refund component allocation id wajib ada.');
        if (trim($customerRefundId) === '') throw new DomainException('Customer refund id wajib ada.');
        if (trim($customerPaymentId) === '') throw new DomainException('Customer payment id wajib ada.');
        if (trim($noteId) === '') throw new DomainException('Note id wajib ada.');
        if (trim($workItemId) === '') throw new DomainException('Work item id wajib ada.');
        if (trim($componentRefId) === '') throw new DomainException('Component ref id wajib ada.');

        PaymentComponentType::assertValid($componentType);

        if (!$refundedAmountRupiah->greaterThan(Money::zero())) {
            throw new DomainException('Refunded amount harus lebih besar dari nol.');
        }

        if ($refundPriority <= 0) {
            throw new DomainException('Refund priority harus lebih besar dari nol.');
        }
    }
}
