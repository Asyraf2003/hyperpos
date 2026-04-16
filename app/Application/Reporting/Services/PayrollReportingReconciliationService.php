<?php

declare(strict_types=1);

namespace App\Application\Reporting\Services;

use RuntimeException;

final class PayrollReportingReconciliationService
{
    public function assertPayrollReportMatches(array $rows, array $expected): void
    {
        $actualRows = count($rows);
        $actualAmount = array_sum(array_map(
            static fn (object $row): int => (int) ($row->toArray()['amount_rupiah'] ?? 0),
            $rows
        ));

        if ($actualRows !== (int) ($expected['total_rows'] ?? 0)) {
            throw new RuntimeException('REPORT_MISMATCH_AMOUNT: total_rows payroll tidak konsisten.');
        }

        if ($actualAmount !== (int) ($expected['total_amount_rupiah'] ?? 0)) {
            throw new RuntimeException('REPORT_MISMATCH_AMOUNT: total_amount payroll tidak konsisten.');
        }
    }
}
