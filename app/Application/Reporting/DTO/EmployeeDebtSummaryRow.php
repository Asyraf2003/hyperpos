<?php

declare(strict_types=1);

namespace App\Application\Reporting\DTO;

final class EmployeeDebtSummaryRow
{
    public function __construct(
        private readonly string $debtId,
        private readonly string $employeeId,
        private readonly string $recordedAt,
        private readonly int $totalDebt,
        private readonly int $totalPaidAmount,
        private readonly int $remainingBalance,
        private readonly string $status,
        private readonly ?string $notes,
    ) {
    }

    public function debtId(): string
    {
        return $this->debtId;
    }

    public function employeeId(): string
    {
        return $this->employeeId;
    }

    public function recordedAt(): string
    {
        return $this->recordedAt;
    }

    public function totalDebt(): int
    {
        return $this->totalDebt;
    }

    public function totalPaidAmount(): int
    {
        return $this->totalPaidAmount;
    }

    public function remainingBalance(): int
    {
        return $this->remainingBalance;
    }

    public function status(): string
    {
        return $this->status;
    }

    public function notes(): ?string
    {
        return $this->notes;
    }

    /**
     * @return array{
     *   debt_id:string,
     *   employee_id:string,
     *   recorded_at:string,
     *   total_debt:int,
     *   total_paid_amount:int,
     *   remaining_balance:int,
     *   status:string,
     *   notes:?string
     * }
     */
    public function toArray(): array
    {
        return [
            'debt_id' => $this->debtId(),
            'employee_id' => $this->employeeId(),
            'recorded_at' => $this->recordedAt(),
            'total_debt' => $this->totalDebt(),
            'total_paid_amount' => $this->totalPaidAmount(),
            'remaining_balance' => $this->remainingBalance(),
            'status' => $this->status(),
            'notes' => $this->notes(),
        ];
    }
}
