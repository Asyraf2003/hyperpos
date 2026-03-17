<?php

declare(strict_types=1);

namespace App\Application\Reporting\Services;

use App\Application\Reporting\DTO\OperationalExpenseSummaryRow;

final class OperationalExpenseReportingReconciliationService
{
    /**
     * @param list<OperationalExpenseSummaryRow> $rows
     * @param array{
     *   total_rows:int,
     *   total_amount_rupiah:int
     * } $expected
     */
    public function assertOperationalExpenseSummaryMatches(array $rows, array $expected): void
    {
        $actualTotalRows = count($rows);
        $actualTotalAmount = 0;

        foreach ($rows as $row) {
            $actualTotalAmount += $row->amountRupiah();
        }

        if ($actualTotalRows !== $expected['total_rows']) {
            throw new \RuntimeException('Reporting mismatch: operational_expense_total_rows.');
        }

        if ($actualTotalAmount !== $expected['total_amount_rupiah']) {
            throw new \RuntimeException('Reporting mismatch: operational_expense_total_amount_rupiah.');
        }
    }
}
