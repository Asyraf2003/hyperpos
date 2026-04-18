<?php

declare(strict_types=1);

namespace App\Adapters\Out\Reporting\Queries\DashboardOperationalPerformance;

use App\Ports\Out\Reporting\DashboardOperationalPerformanceReaderPort;

/**
 * @phpstan-import-type DashboardOperationalPerformancePeriodRow from DashboardOperationalPerformanceReaderPort
 */
final class DashboardOperationalPerformancePeriodProfitCalculator
{
    /**
     * @param array<string, DashboardOperationalPerformancePeriodRow> $rowsByKey
     */
    public function calculate(array &$rowsByKey): void
    {
        foreach ($rowsByKey as $key => $row) {
            $rowsByKey[$key]['operational_profit_rupiah'] =
                $row['cash_in_rupiah']
                - $row['refund_rupiah']
                - $row['external_purchase_cost_rupiah']
                - $row['store_stock_cogs_rupiah']
                - $row['operational_expense_rupiah']
                - $row['payroll_disbursement_rupiah']
                - $row['employee_debt_cash_out_rupiah'];
        }
    }
}
