<?php

declare(strict_types=1);

namespace App\Application\Reporting\DTO;

final class PayrollReportRow
{
    public function __construct(
        private readonly string $id,
        private readonly string $employeeId,
        private readonly string $employeeName,
        private readonly int $amountRupiah,
        private readonly string $disbursementDate,
        private readonly string $modeValue,
        private readonly string $modeLabel,
        private readonly ?string $notes,
    ) {
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'employee_id' => $this->employeeId,
            'employee_name' => $this->employeeName,
            'amount_rupiah' => $this->amountRupiah,
            'disbursement_date' => $this->disbursementDate,
            'mode_value' => $this->modeValue,
            'mode_label' => $this->modeLabel,
            'notes' => $this->notes,
        ];
    }
}
