<?php

declare(strict_types=1);

namespace App\Ports\Out\EmployeeFinance;

interface EmployeeListPageReaderPort
{
    /**
     * @return list<array{
     *     id: string,
     *     employee_name: string,
     *     phone: ?string,
     *     default_salary_amount: ?int,
     *     default_salary_amount_formatted: ?string,
     *     salary_basis_type: string,
     *     salary_basis_label: string,
     *     employment_status: string,
     *     employment_status_label: string
     * }>
     */
    public function all(): array;
}
