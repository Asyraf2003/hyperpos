<?php

declare(strict_types=1);

namespace App\Core\EmployeeFinance\EmployeeDebt;

use App\Core\Shared\ValueObjects\Money;
use DateTimeImmutable;
use InvalidArgumentException;

class DebtPayment
{
    public function __construct(
        private string $id,
        private Money $amount,
        private DateTimeImmutable $paymentDate,
        private ?string $notes = null
    ) {
        if ($amount->isZero() || $amount->isNegative()) {
            throw new InvalidArgumentException("Nominal pembayaran hutang harus lebih dari nol.");
        }
    }

    public function getId(): string { return $this->id; }
    public function getAmount(): Money { return $this->amount; }
    public function getPaymentDate(): DateTimeImmutable { return $this->paymentDate; }
    public function getNotes(): ?string { return $this->notes; }
}
