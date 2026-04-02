<?php

declare(strict_types=1);

namespace App\Core\Payment\PaymentComponentAllocation;

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
        PaymentComponentAllocationGuard::assertValid(
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
}
