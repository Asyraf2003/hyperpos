<?php

declare(strict_types=1);

namespace App\Adapters\Out\EmployeeFinance;

use Illuminate\Support\Carbon;

final class EmployeeDetailVersionIdentityMapper
{
    public function __construct(
        private EmployeeDetailSnapshotReader $snapshotReader,
        private EmployeeDetailLabelFormatter $labelFormatter,
    ) {
    }

    public function map(object $row): array
    {
        $snapshot = $this->snapshotReader->decode((string) $row->snapshot_json);
        $defaultSalaryAmount = $this->snapshotReader->nullableInt($snapshot, 'default_salary_amount');
        $salaryBasisType = (string) ($snapshot['salary_basis_type'] ?? 'manual');
        $employmentStatus = (string) ($snapshot['employment_status'] ?? 'active');

        return [
            'employee_name' => (string) ($snapshot['employee_name'] ?? '-'),
            'phone' => $this->snapshotReader->nullableString($snapshot, 'phone'),
            'salary_basis_type' => $salaryBasisType,
            'salary_basis_label' => $this->labelFormatter->salaryBasis($salaryBasisType),
            'default_salary_amount' => $defaultSalaryAmount,
            'default_salary_amount_formatted' => $defaultSalaryAmount === null ? null : number_format($defaultSalaryAmount, 0, ',', '.'),
            'default_salary_amount_label' => $defaultSalaryAmount === null ? '-' : 'Rp'.number_format($defaultSalaryAmount, 0, ',', '.'),
            'employment_status' => $employmentStatus,
            'employment_status_label' => $this->labelFormatter->employmentStatus($employmentStatus),
            'started_at' => $this->snapshotReader->nullableString($snapshot, 'started_at'),
            'ended_at' => $this->snapshotReader->nullableString($snapshot, 'ended_at'),
            'changed_at' => Carbon::parse((string) $row->changed_at)->format('Y-m-d H:i'),
        ];
    }
}
