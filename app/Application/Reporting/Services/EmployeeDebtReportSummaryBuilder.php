<?php

declare(strict_types=1);

namespace App\Application\Reporting\Services;

final class EmployeeDebtReportSummaryBuilder
{
    public function build(array $rows, array $statusRows): array
    {
        $paidRows = 0;
        $unpaidRows = 0;

        foreach ($statusRows as $statusRow) {
            if (($statusRow['status'] ?? null) === 'paid') {
                $paidRows += (int) ($statusRow['total_rows'] ?? 0);
            }

            if (($statusRow['status'] ?? null) === 'unpaid') {
                $unpaidRows += (int) ($statusRow['total_rows'] ?? 0);
            }
        }

        return [
            'total_rows' => count($rows),
            'total_debt' => array_sum(array_column($rows, 'total_debt')),
            'total_paid_amount' => array_sum(array_column($rows, 'total_paid_amount')),
            'total_remaining_balance' => array_sum(array_column($rows, 'remaining_balance')),
            'paid_rows' => $paidRows,
            'unpaid_rows' => $unpaidRows,
        ];
    }
}
