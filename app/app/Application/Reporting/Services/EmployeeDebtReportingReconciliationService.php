<?php

declare(strict_types=1);

namespace App\Application\Reporting\Services;

use App\Application\Reporting\DTO\EmployeeDebtSummaryRow;

final class EmployeeDebtReportingReconciliationService
{
    /**
     * @param list<EmployeeDebtSummaryRow> $rows
     * @param array{
     *   total_rows:int,
     *   total_debt:int,
     *   total_paid_amount:int,
     *   total_remaining_balance:int
     * } $expected
     */
    public function assertEmployeeDebtSummaryMatches(array $rows, array $expected): void
    {
        $actualTotalRows = count($rows);
        $actualTotalDebt = 0;
        $actualTotalPaidAmount = 0;
        $actualTotalRemainingBalance = 0;

        foreach ($rows as $row) {
            $actualTotalDebt += $row->totalDebt();
            $actualTotalPaidAmount += $row->totalPaidAmount();
            $actualTotalRemainingBalance += $row->remainingBalance();
        }

        if ($actualTotalRows !== $expected['total_rows']) {
            throw new \RuntimeException('Reporting mismatch: employee_debt_total_rows.');
        }

        if ($actualTotalDebt !== $expected['total_debt']) {
            throw new \RuntimeException('Reporting mismatch: employee_debt_total_debt.');
        }

        if ($actualTotalPaidAmount !== $expected['total_paid_amount']) {
            throw new \RuntimeException('Reporting mismatch: employee_debt_total_paid_amount.');
        }

        if ($actualTotalRemainingBalance !== $expected['total_remaining_balance']) {
            throw new \RuntimeException('Reporting mismatch: employee_debt_total_remaining_balance.');
        }
    }
}
