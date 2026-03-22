<?php

declare(strict_types=1);

namespace App\Ports\Out\EmployeeFinance;

interface EmployeeDebtAdjustmentWriterPort
{
    /**
     * @param array<string, mixed> $record
     */
    public function record(array $record): void;
}
