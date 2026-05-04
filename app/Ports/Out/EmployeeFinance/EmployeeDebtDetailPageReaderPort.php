<?php

declare(strict_types=1);

namespace App\Ports\Out\EmployeeFinance;

interface EmployeeDebtDetailPageReaderPort
{
    /**
     * @return array<string, mixed>|null
     */
    public function findById(string $debtId): ?array;
}
