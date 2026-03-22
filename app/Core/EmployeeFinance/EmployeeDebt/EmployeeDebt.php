<?php

declare(strict_types=1);

namespace App\Core\EmployeeFinance\EmployeeDebt;

use App\Core\Shared\Exceptions\DomainException;
use App\Core\Shared\ValueObjects\Money;
use InvalidArgumentException;

class EmployeeDebt
{
    /** @var array<string, DebtPayment> */
    private array $payments = [];

    public function __construct(
        private string $id,
        private string $employeeId,
        private Money $totalDebt,
        private Money $remainingBalance,
        private DebtStatus $status,
        private ?string $notes = null
    ) {
        if ($totalDebt->isZero() || $totalDebt->isNegative()) {
            throw new InvalidArgumentException('Total hutang awal harus lebih dari nol.');
        }
    }

    public static function record(string $id, string $employeeId, Money $amount, ?string $notes = null): self
    {
        return new self($id, $employeeId, $amount, $amount, DebtStatus::UNPAID, $notes);
    }

    /** @param array<string, DebtPayment> $payments */
    public static function rehydrate(
        string $id,
        string $employeeId,
        Money $totalDebt,
        Money $remainingBalance,
        DebtStatus $status,
        ?string $notes,
        array $payments
    ): self {
        $instance = new self($id, $employeeId, $totalDebt, $remainingBalance, $status, $notes);
        $instance->payments = $payments;
        return $instance;
    }

    public function pay(DebtPayment $payment): void
    {
        if ($this->status === DebtStatus::PAID) {
            throw new DomainException('Hutang ini sudah lunas, tidak dapat menerima pembayaran lagi.');
        }
        if ($payment->getAmount()->greaterThan($this->remainingBalance)) {
            throw new DomainException('Nominal pembayaran melebihi sisa hutang.');
        }

        $this->payments[$payment->getId()] = $payment;
        $this->remainingBalance = $this->remainingBalance->subtract($payment->getAmount());

        if ($this->remainingBalance->isZero()) {
            $this->status = DebtStatus::PAID;
        }
    }

    public function adjustPrincipal(string $type, Money $amount): void
    {
        if ($type === 'increase') {
            $this->totalDebt = $this->totalDebt->add($amount);
            $this->remainingBalance = $this->remainingBalance->add($amount);
            $this->status = DebtStatus::UNPAID;
            return;
        }
        if ($type !== 'decrease') {
            throw new InvalidArgumentException('Tipe koreksi hutang tidak valid.');
        }
        if ($amount->greaterThan($this->remainingBalance)) {
            throw new DomainException('Koreksi pengurangan melebihi sisa hutang.');
        }

        $newTotalDebt = $this->totalDebt->subtract($amount);
        if ($newTotalDebt->isZero() || $newTotalDebt->isNegative()) {
            throw new DomainException('Koreksi tidak boleh membuat total hutang nol atau negatif.');
        }

        $this->totalDebt = $newTotalDebt;
        $this->remainingBalance = $this->remainingBalance->subtract($amount);
        $this->status = $this->remainingBalance->isZero() ? DebtStatus::PAID : DebtStatus::UNPAID;
    }

    public function getId(): string { return $this->id; }
    public function getEmployeeId(): string { return $this->employeeId; }
    public function getTotalDebt(): Money { return $this->totalDebt; }
    public function getRemainingBalance(): Money { return $this->remainingBalance; }
    public function getStatus(): DebtStatus { return $this->status; }
    public function getNotes(): ?string { return $this->notes; }
    /** @return array<string, DebtPayment> */
    public function getPayments(): array { return $this->payments; }
}
