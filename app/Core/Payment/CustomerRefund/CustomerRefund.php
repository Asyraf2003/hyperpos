<?php

declare(strict_types=1);

namespace App\Core\Payment\CustomerRefund;

use App\Core\Shared\Exceptions\DomainException;
use App\Core\Shared\ValueObjects\Money;
use DateTimeImmutable;

final class CustomerRefund
{
    private function __construct(
        private string $id,
        private string $customerPaymentId,
        private string $noteId,
        private Money $amountRupiah,
        private DateTimeImmutable $refundedAt,
        private string $reason,
    ) {
    }

    public static function create(
        string $id,
        string $customerPaymentId,
        string $noteId,
        Money $amountRupiah,
        DateTimeImmutable $refundedAt,
        string $reason,
    ): self {
        self::assertValid($id, $customerPaymentId, $noteId, $amountRupiah, $reason);
        return new self(trim($id), trim($customerPaymentId), trim($noteId), $amountRupiah, $refundedAt, trim($reason));
    }

    public static function rehydrate(
        string $id,
        string $customerPaymentId,
        string $noteId,
        Money $amountRupiah,
        DateTimeImmutable $refundedAt,
        string $reason,
    ): self {
        self::assertValid($id, $customerPaymentId, $noteId, $amountRupiah, $reason);
        return new self(trim($id), trim($customerPaymentId), trim($noteId), $amountRupiah, $refundedAt, trim($reason));
    }

    public function id(): string { return $this->id; }
    public function customerPaymentId(): string { return $this->customerPaymentId; }
    public function noteId(): string { return $this->noteId; }
    public function amountRupiah(): Money { return $this->amountRupiah; }
    public function refundedAt(): DateTimeImmutable { return $this->refundedAt; }
    public function reason(): string { return $this->reason; }

    private static function assertValid(
        string $id,
        string $customerPaymentId,
        string $noteId,
        Money $amountRupiah,
        string $reason,
    ): void {
        if (trim($id) === '') throw new DomainException('Customer refund id wajib ada.');
        if (trim($customerPaymentId) === '') throw new DomainException('Customer payment id pada customer refund wajib ada.');
        if (trim($noteId) === '') throw new DomainException('Note id pada customer refund wajib ada.');
        if (trim($reason) === '') throw new DomainException('Reason pada customer refund wajib ada.');
        if ($amountRupiah->greaterThan(Money::zero()) === false) {
            throw new DomainException('Amount rupiah pada customer refund harus lebih besar dari nol.');
        }
    }
}
