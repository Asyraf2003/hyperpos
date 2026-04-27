<?php

declare(strict_types=1);

namespace App\Core\Payment\CustomerPayment;

use App\Core\Shared\Exceptions\DomainException;
use App\Core\Shared\ValueObjects\Money;

final class CustomerPaymentCashDetail
{
    private function __construct(
        private string $customerPaymentId,
        private Money $amountPaidRupiah,
        private Money $amountReceivedRupiah,
        private Money $changeRupiah,
    ) {
    }

    public static function create(
        string $customerPaymentId,
        Money $amountPaidRupiah,
        Money $amountReceivedRupiah,
    ): self {
        $changeRupiah = $amountReceivedRupiah->subtract($amountPaidRupiah);

        self::assertValid($customerPaymentId, $amountPaidRupiah, $amountReceivedRupiah, $changeRupiah);

        return new self(
            trim($customerPaymentId),
            $amountPaidRupiah,
            $amountReceivedRupiah,
            $changeRupiah,
        );
    }

    public function customerPaymentId(): string
    {
        return $this->customerPaymentId;
    }

    public function amountPaidRupiah(): Money
    {
        return $this->amountPaidRupiah;
    }

    public function amountReceivedRupiah(): Money
    {
        return $this->amountReceivedRupiah;
    }

    public function changeRupiah(): Money
    {
        return $this->changeRupiah;
    }

    private static function assertValid(
        string $customerPaymentId,
        Money $amountPaidRupiah,
        Money $amountReceivedRupiah,
        Money $changeRupiah,
    ): void {
        if (trim($customerPaymentId) === '') {
            throw new DomainException('Customer payment id pada detail cash wajib ada.');
        }

        if ($amountPaidRupiah->greaterThan(Money::zero()) === false) {
            throw new DomainException('Nominal dibayar pada detail cash harus lebih besar dari nol.');
        }

        if ($amountReceivedRupiah->greaterThan(Money::zero()) === false) {
            throw new DomainException('Uang diterima pada detail cash harus lebih besar dari nol.');
        }

        if ($changeRupiah->isNegative()) {
            throw new DomainException('Uang diterima cash tidak boleh kurang dari nominal dibayar.');
        }
    }
}
