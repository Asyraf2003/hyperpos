<?php

declare(strict_types=1);

namespace App\Core\Payment\RefundComponentAllocation;

use App\Core\Shared\ValueObjects\Money;

final class RefundComponentAllocation
{
    private function __construct(
        private string $id,
        private string $customerRefundId,
        private string $customerPaymentId,
        private string $noteId,
        private string $workItemId,
        private string $componentType,
        private string $componentRefId,
        private Money $refundedAmountRupiah,
        private int $refundPriority,
    ) {
    }

    public static function create(
        string $id,
        string $customerRefundId,
        string $customerPaymentId,
        string $noteId,
        string $workItemId,
        string $componentType,
        string $componentRefId,
        Money $refundedAmountRupiah,
        int $refundPriority,
    ): self {
        RefundComponentAllocationGuard::assertValid(
            $id,
            $customerRefundId,
            $customerPaymentId,
            $noteId,
            $workItemId,
            $componentType,
            $componentRefId,
            $refundedAmountRupiah,
            $refundPriority,
        );

        return new self(
            trim($id),
            trim($customerRefundId),
            trim($customerPaymentId),
            trim($noteId),
            trim($workItemId),
            trim($componentType),
            trim($componentRefId),
            $refundedAmountRupiah,
            $refundPriority,
        );
    }

    public static function rehydrate(
        string $id,
        string $customerRefundId,
        string $customerPaymentId,
        string $noteId,
        string $workItemId,
        string $componentType,
        string $componentRefId,
        Money $refundedAmountRupiah,
        int $refundPriority,
    ): self {
        return self::create(
            $id,
            $customerRefundId,
            $customerPaymentId,
            $noteId,
            $workItemId,
            $componentType,
            $componentRefId,
            $refundedAmountRupiah,
            $refundPriority,
        );
    }

    public function id(): string { return $this->id; }
    public function customerRefundId(): string { return $this->customerRefundId; }
    public function customerPaymentId(): string { return $this->customerPaymentId; }
    public function noteId(): string { return $this->noteId; }
    public function workItemId(): string { return $this->workItemId; }
    public function componentType(): string { return $this->componentType; }
    public function componentRefId(): string { return $this->componentRefId; }
    public function refundedAmountRupiah(): Money { return $this->refundedAmountRupiah; }
    public function refundPriority(): int { return $this->refundPriority; }
}
