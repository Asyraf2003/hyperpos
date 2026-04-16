<?php

declare(strict_types=1);

namespace App\Ports\Out\Reporting;

interface PayrollReportingSourceReaderPort
{
    public function getPayrollReportRows(string $fromDate, string $toDate): array;

    public function getPayrollReportReconciliation(string $fromDate, string $toDate): array;
}
