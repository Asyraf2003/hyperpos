<?php

declare(strict_types=1);

namespace App\Adapters\Out\EmployeeFinance;

final class EmployeeDetailCurrentIdentityMapper
{
    public function __construct(private EmployeeDetailLabelFormatter $labelFormatter)
    {
    }

    public function map(object $row): array
    {
        $defaultSalaryAmountRaw = $row->default_salary_amount ?? null;
        $defaultSalaryAmount = $defaultSalaryAmountRaw === null ? null : (int) $defaultSalaryAmountRaw;
        $salaryBasisType = (string) $row->salary_basis_type;
        $employmentStatus = (string) $row->employment_status;

        return [
            'id' => (string) $row->id,
            'employee_name' => (string) $row->employee_name,
            'phone' => $this->nullableString($row->phone ?? null),
            'salary_basis_type' => $salaryBasisType,
            'salary_basis_label' => $this->labelFormatter->salaryBasis($salaryBasisType),
            'default_salary_amount' => $defaultSalaryAmount,
            'default_salary_amount_formatted' => $defaultSalaryAmount === null ? null : number_format($defaultSalaryAmount, 0, ',', '.'),
            'default_salary_amount_label' => $defaultSalaryAmount === null ? '-' : 'Rp'.number_format($defaultSalaryAmount, 0, ',', '.'),
            'employment_status' => $employmentStatus,
            'employment_status_label' => $this->labelFormatter->employmentStatus($employmentStatus),
            'started_at' => $this->nullableString($row->started_at ?? null),
            'ended_at' => $this->nullableString($row->ended_at ?? null),
        ];
    }

    private function nullableString(mixed $value): ?string
    {
        return $value === null ? null : (string) $value;
    }
}
