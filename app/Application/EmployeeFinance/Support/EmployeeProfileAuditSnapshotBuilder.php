<?php

declare(strict_types=1);

namespace App\Application\EmployeeFinance\Support;

use App\Core\EmployeeFinance\Employee\Employee;
use DateTimeImmutable;

final class EmployeeProfileAuditSnapshotBuilder
{
    /**
     * @return array{
     *   employee_name:string,
     *   name:string,
     *   phone:?string,
     *   default_salary_amount:?int,
     *   base_salary_amount:?int,
     *   salary_basis_type:string,
     *   pay_period_value:string,
     *   employment_status:string,
     *   status_value:string,
     *   started_at:?string,
     *   ended_at:?string
     * }
     */
    public function build(Employee $employee): array
    {
        $defaultSalaryAmount = $employee->getDefaultSalaryAmount()?->amount();
        $salaryBasisType = $employee->getSalaryBasisType()->value;
        $employmentStatus = $employee->getEmploymentStatus()->value;

        return [
            'employee_name' => $employee->getEmployeeName(),
            'name' => $employee->getName(),
            'phone' => $employee->getPhone(),
            'default_salary_amount' => $defaultSalaryAmount,
            'base_salary_amount' => $defaultSalaryAmount,
            'salary_basis_type' => $salaryBasisType,
            'pay_period_value' => $salaryBasisType,
            'employment_status' => $employmentStatus,
            'status_value' => $employmentStatus,
            'started_at' => $this->formatDate($employee->getStartedAt()),
            'ended_at' => $this->formatDate($employee->getEndedAt()),
        ];
    }

    private function formatDate(?DateTimeImmutable $value): ?string
    {
        return $value?->format('Y-m-d');
    }
}
