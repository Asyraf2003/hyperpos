<?php

declare(strict_types=1);

namespace App\Core\Payment\PaymentComponentAllocation;

use App\Core\Shared\Exceptions\DomainException;
use App\Core\Shared\ValueObjects\Money;

final class PaymentComponentAllocation
{
    private function __construct(
        private string $id,
        private string $customerPaymentId,
        private string $noteId,
        private string $workItemId,
        private string $componentType,
        private string $componentRefId,
        private Money $componentAmountRupiahSnapshot,
        private Money $allocatedAmountRupiah,
        private int $allocationPriority,
    ) {
    }

    public static function create(
        string $id,
        string $customerPaymentId,
        string $noteId,
        string $workItemId,
        string $componentType,
        string $componentRefId,
        Money $componentAmountRupiahSnapshot,
        Money $allocatedAmountRupiah,
        int $allocationPriority,
    ): self {
        self::assertValid(
            $id,
            $customerPaymentId,
            $noteId,
            $workItemId,
            $componentType,
            $componentRefId,
            $componentAmountRupiahSnapshot,
            $allocatedAmountRupiah,
            $allocationPriority,
        );

        return new self(
            trim($id),
            trim($customerPaymentId),
            trim($noteId),
            trim($workItemId),
            trim($componentType),
            trim($componentRefId),
            $componentAmountRupiahSnapshot,
            $allocatedAmountRupiah,
            $allocationPriority,
        );
    }

    public static function rehydrate(
        string $id,
        string $customerPaymentId,
        string $noteId,
        string $workItemId,
        string $componentType,
        string $componentRefId,
        Money $componentAmountRupiahSnapshot,
        Money $allocatedAmountRupiah,
        int $allocationPriority,
    ): self {
        return self::create(
            $id,
            $customerPaymentId,
            $noteId,
            $workItemId,
            $componentType,
            $componentRefId,
            $componentAmountRupiahSnapshot,
            $allocatedAmountRupiah,
            $allocationPriority,
        );
    }

    public function id(): string { return $this->id; }
    public function customerPaymentId(): string { return $this->customerPaymentId; }
    public function noteId(): string { return $this->noteId; }
    public function workItemId(): string { return $this->workItemId; }
    public function componentType(): string { return $this->componentType; }
    public function componentRefId(): string { return $this->componentRefId; }
    public function componentAmountRupiahSnapshot(): Money { return $this->componentAmountRupiahSnapshot; }
    public function allocatedAmountRupiah(): Money { return $this->allocatedAmountRupiah; }
    public function allocationPriority(): int { return $this->allocationPriority; }

    private static function assertValid(
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
