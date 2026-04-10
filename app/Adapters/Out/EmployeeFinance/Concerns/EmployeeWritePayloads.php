<?php

declare(strict_types=1);

namespace App\Adapters\Out\EmployeeFinance\Concerns;

use App\Core\EmployeeFinance\Employee\Employee;

trait EmployeeWritePayloads
{
    /**
     * @return array<string, string|int|null|\DateTimeImmutable>
     */
    private function toCreateEmployeeRecord(Employee $employee, \DateTimeImmutable $occurredAt): array
    {
        return [
            'id' => $employee->getId(),
            'employee_name' => $employee->getEmployeeName(),
            'phone' => $employee->getPhone(),
            'salary_basis_type' => $employee->getSalaryBasisType()->value,
            'default_salary_amount' => $employee->getDefaultSalaryAmount()?->amount(),
            'employment_status' => $employee->getEmploymentStatus()->value,
            'started_at' => $employee->getStartedAt()?->format('Y-m-d'),
            'ended_at' => $employee->getEndedAt()?->format('Y-m-d'),
            'created_at' => $occurredAt,
            'updated_at' => $occurredAt,
        ];
    }

    /**
     * @return array<string, string|int|null|\DateTimeImmutable>
     */
    private function toUpdateEmployeeRecord(Employee $employee, \DateTimeImmutable $occurredAt): array
    {
        return [
            'employee_name' => $employee->getEmployeeName(),
            'phone' => $employee->getPhone(),
            'salary_basis_type' => $employee->getSalaryBasisType()->value,
            'default_salary_amount' => $employee->getDefaultSalaryAmount()?->amount(),
            'employment_status' => $employee->getEmploymentStatus()->value,
            'started_at' => $employee->getStartedAt()?->format('Y-m-d'),
            'ended_at' => $employee->getEndedAt()?->format('Y-m-d'),
            'updated_at' => $occurredAt,
        ];
    }

    /**
     * @return array{
     *   employee_name:string,
     *   phone:?string,
     *   salary_basis_type:string,
     *   default_salary_amount:?int,
     *   employment_status:string,
     *   started_at:?string,
     *   ended_at:?string
     * }
     */
    private function toSnapshot(Employee $employee): array
    {
        return [
            'employee_name' => $employee->getEmployeeName(),
            'phone' => $employee->getPhone(),
            'salary_basis_type' => $employee->getSalaryBasisType()->value,
            'default_salary_amount' => $employee->getDefaultSalaryAmount()?->amount(),
            'employment_status' => $employee->getEmploymentStatus()->value,
            'started_at' => $employee->getStartedAt()?->format('Y-m-d'),
            'ended_at' => $employee->getEndedAt()?->format('Y-m-d'),
        ];
    }

    /**
     * @return array{
     *   employee_name:string,
     *   phone:?string,
     *   salary_basis_type:string,
     *   default_salary_amount:?int,
     *   employment_status:string,
     *   started_at:?string,
     *   ended_at:?string
     * }
     */
    private function snapshotFromRow(object $row): array
    {
        return [
            'employee_name' => (string) $row->employee_name,
            'phone' => $row->phone !== null ? (string) $row->phone : null,
            'salary_basis_type' => (string) $row->salary_basis_type,
            'default_salary_amount' => $row->default_salary_amount !== null ? (int) $row->default_salary_amount : null,
            'employment_status' => (string) $row->employment_status,
            'started_at' => $row->started_at !== null ? (string) $row->started_at : null,
            'ended_at' => $row->ended_at !== null ? (string) $row->ended_at : null,
        ];
    }
}
