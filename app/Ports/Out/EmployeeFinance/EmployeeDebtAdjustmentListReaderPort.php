<?php

declare(strict_types=1);

namespace App\Ports\Out\EmployeeFinance;

interface EmployeeDebtAdjustmentListReaderPort
{
    /**
     * @return list<array<string, mixed>>
     */
    public function findByDebtId(string $debtId): array;
}
