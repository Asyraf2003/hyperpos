<?php

declare(strict_types=1);

namespace App\Core\EmployeeFinance\EmployeeDebt;

use App\Core\Shared\ValueObjects\Money;
use App\Core\Shared\Exceptions\DomainException;
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
            throw new InvalidArgumentException("Total hutang awal harus lebih dari nol.");
        }
    }

    public static function record(
        string $id,
        string $employeeId,
        Money $amount,
        ?string $notes = null
    ): self {
        return new self(
            $id,
            $employeeId,
            $amount,
            $amount,
            DebtStatus::UNPAID,
            $notes
        );
    }

    /**
     * @param array<string, DebtPayment> $payments
     */
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
            throw new DomainException("Hutang ini sudah lunas, tidak dapat menerima pembayaran lagi.");
        }

        if ($payment->getAmount()->greaterThan($this->remainingBalance)) {
            throw new DomainException("Nominal pembayaran melebihi sisa hutang.");
        }

        $this->payments[$payment->getId()] = $payment;
        $this->remainingBalance = $this->remainingBalance->subtract($payment->getAmount());

        if ($this->remainingBalance->isZero()) {
            $this->status = DebtStatus::PAID;
        }
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
