<?php

declare(strict_types=1);

namespace App\Ports\Out\EmployeeFinance;

interface EmployeeDetailPageReaderPort
{
    /**
     * @return array{
     *     summary: array<string, mixed>,
     *     page: array<string, mixed>
     * }|null
     */
    public function findById(string $employeeId): ?array;
}
