<?php

declare(strict_types=1);

namespace App\Core\Payment\PaymentComponentAllocation;

use App\Core\Shared\Exceptions\DomainException;
use App\Core\Shared\ValueObjects\Money;

final class PaymentComponentAllocationGuard
{
    public static function assertValid(
        string $id,
        string $customerPaymentId,
        string $noteId,
        string $workItemId,
        string $componentType,
        string $componentRefId,
        Money $componentAmountRupiahSnapshot,
        Money $allocatedAmountRupiah,
        int $allocationPriority,
    ): void {
        if (trim($id) === '') throw new DomainException('Payment component allocation id wajib ada.');
        if (trim($customerPaymentId) === '') throw new DomainException('Customer payment id wajib ada.');
        if (trim($noteId) === '') throw new DomainException('Note id wajib ada.');
        if (trim($workItemId) === '') throw new DomainException('Work item id wajib ada.');
        if (trim($componentRefId) === '') throw new DomainException('Component ref id wajib ada.');

        PaymentComponentType::assertValid($componentType);

        if (!$componentAmountRupiahSnapshot->greaterThan(Money::zero())) {
            throw new DomainException('Component amount snapshot harus lebih besar dari nol.');
        }

        if (!$allocatedAmountRupiah->greaterThan(Money::zero())) {
            throw new DomainException('Allocated amount harus lebih besar dari nol.');
        }

        if ($allocatedAmountRupiah->greaterThan($componentAmountRupiahSnapshot)) {
            throw new DomainException('Allocated amount tidak boleh melebihi snapshot komponen.');
        }

        if ($allocationPriority <= 0) {
            throw new DomainException('Allocation priority harus lebih besar dari nol.');
        }
    }
}
