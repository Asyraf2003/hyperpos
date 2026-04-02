<?php

declare(strict_types=1);

namespace App\Core\Payment\RefundComponentAllocation;

use App\Core\Payment\PaymentComponentAllocation\PaymentComponentType;
use App\Core\Shared\Exceptions\DomainException;
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
        self::assertValid(
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

    private static function assertValid(
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
