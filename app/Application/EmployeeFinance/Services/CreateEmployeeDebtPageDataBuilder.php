<?php

declare(strict_types=1);

namespace App\Application\EmployeeFinance\Services;

final class CreateEmployeeDebtPageDataBuilder
{
    public function __construct(
        private readonly EmployeeListPageData $employees,
    ) {
    }

    /**
     * @return array{
     *     employees: list<array{
     *         id: string,
     *         employee_name: string,
     *         phone: ?string,
     *         default_salary_amount: ?int,
     *         default_salary_amount_formatted: ?string,
     *         salary_basis_type: string,
     *         salary_basis_label: string,
     *         employment_status: string,
     *         employment_status_label: string
     *     }>,
     *     prefilledEmployeeId: ?string,
     *     prefilledEmployeeName: ?string
     * }
     */
    public function build(mixed $employeeIdCandidate): array
    {
        $employees = $this->employees->all();
        $prefilledEmployeeId = $this->resolvePrefilledEmployeeId($employeeIdCandidate, $employees);

        return [
            'employees' => $employees,
            'prefilledEmployeeId' => $prefilledEmployeeId,
            'prefilledEmployeeName' => $this->resolvePrefilledEmployeeName($prefilledEmployeeId, $employees),
        ];
    }

    /**
     * @param list<array{id:string,employee_name:string}> $employees
     */
    private function resolvePrefilledEmployeeId(mixed $candidate, array $employees): ?string
    {
        if (! is_string($candidate)) {
            return null;
        }

        $candidate = trim($candidate);

        if ($candidate === '') {
            return null;
        }

        foreach ($employees as $employee) {
            if ($employee['id'] === $candidate) {
                return $candidate;
            }
        }

        return null;
    }

    /**
     * @param list<array{id:string,employee_name:string}> $employees
     */
    private function resolvePrefilledEmployeeName(?string $employeeId, array $employees): ?string
    {
        if ($employeeId === null) {
            return null;
        }

        foreach ($employees as $employee) {
            if ($employee['id'] === $employeeId) {
                return $employee['employee_name'];
            }
        }

        return null;
    }
}
