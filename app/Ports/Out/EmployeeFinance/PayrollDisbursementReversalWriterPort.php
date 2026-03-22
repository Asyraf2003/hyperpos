<?php

declare(strict_types=1);

namespace App\Ports\Out\EmployeeFinance;

interface PayrollDisbursementReversalWriterPort
{
    /**
     * @param array<string, mixed> $record
     */
    public function record(array $record): void;
}
