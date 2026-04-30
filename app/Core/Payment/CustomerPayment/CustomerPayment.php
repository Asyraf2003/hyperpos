<?php

declare(strict_types=1);

namespace App\Core\Payment\CustomerPayment;

use App\Core\Shared\Exceptions\DomainException;
use App\Core\Shared\ValueObjects\Money;
use DateTimeImmutable;

final class CustomerPayment
{
    public const METHOD_CASH = CustomerPaymentMethod::CASH;
    public const METHOD_TRANSFER = CustomerPaymentMethod::TRANSFER;
    public const METHOD_UNKNOWN = CustomerPaymentMethod::UNKNOWN;

    private function __construct(
        private string $id,
        private Money $amountRupiah,
        private DateTimeImmutable $paidAt,
        private string $paymentMethod,
    ) {
    }

    public static function create(
        string $id,
        Money $amountRupiah,
        DateTimeImmutable $paidAt,
        string $paymentMethod = self::METHOD_UNKNOWN,
    ): self {
        $normalizedPaymentMethod = CustomerPaymentMethod::normalize($paymentMethod);
        self::assertValid($id, $amountRupiah, $normalizedPaymentMethod);

        return new self(
            trim($id),
            $amountRupiah,
            $paidAt,
            $normalizedPaymentMethod,
        );
    }

    public static function rehydrate(
        string $id,
        Money $amountRupiah,
        DateTimeImmutable $paidAt,
        string $paymentMethod = self::METHOD_UNKNOWN,
    ): self {
        $normalizedPaymentMethod = CustomerPaymentMethod::normalize($paymentMethod);
        self::assertValid($id, $amountRupiah, $normalizedPaymentMethod);

        return new self(
            trim($id),
            $amountRupiah,
            $paidAt,
            $normalizedPaymentMethod,
        );
    }

    public function id(): string
    {
        return $this->id;
    }

    public function amountRupiah(): Money
    {
        return $this->amountRupiah;
    }

    public function paidAt(): DateTimeImmutable
    {
        return $this->paidAt;
    }

    public function paymentMethod(): string
    {
        return $this->paymentMethod;
    }

    private static function assertValid(
        string $id,
        Money $amountRupiah,
        string $paymentMethod,
    ): void {
        if (trim($id) === '') {
            throw new DomainException('Customer payment id wajib ada.');
        }

        if ($amountRupiah->greaterThan(Money::zero()) === false) {
            throw new DomainException('Amount rupiah pada customer payment harus lebih besar dari nol.');
        }

        CustomerPaymentMethod::assertValid($paymentMethod);
    }
}
