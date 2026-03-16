<?php

declare(strict_types=1);

namespace App\Core\EmployeeFinance\Payroll;

use App\Core\Shared\ValueObjects\Money;
use DateTimeImmutable;
use InvalidArgumentException;

class PayrollDisbursement
{
    public function __construct(
        private string $id,
        private string $employeeId,
        private Money $amount,
        private DateTimeImmutable $disbursementDate,
        private DisbursementMode $mode,
        private ?string $notes = null
    ) {
        if ($amount->isZero()) {
            throw new InvalidArgumentException('Nominal pencairan gaji harus lebih dari nol.');
        }

        if ($amount->isNegative()) {
            throw new InvalidArgumentException('Nominal pencairan gaji tidak boleh negatif.');
        }
    }

    public static function disburse(
        string $id,
        string $employeeId,
        Money $amount,
        DateTimeImmutable $disbursementDate,
        DisbursementMode $mode,
        ?string $notes = null
    ): self {
        return new self(
            $id,
            $employeeId,
            $amount,
            $disbursementDate,
            $mode,
            $notes
        );
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getEmployeeId(): string
    {
        return $this->employeeId;
    }

    public function getAmount(): Money
    {
        return $this->amount;
    }

    public function getDisbursementDate(): DateTimeImmutable
    {
        return $this->disbursementDate;
    }

    public function getMode(): DisbursementMode
    {
        return $this->mode;
    }

    public function getNotes(): ?string
    {
        return $this->notes;
    }
}
