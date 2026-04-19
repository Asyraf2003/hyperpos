<?php

declare(strict_types=1);

namespace App\Adapters\Out\Reporting\Queries\DashboardOperationalPerformance;

use App\Ports\Out\Reporting\DashboardOperationalPerformanceReaderPort;

/**
 * @phpstan-import-type DashboardOperationalPerformancePeriodRow from DashboardOperationalPerformanceReaderPort
 * @phpstan-type DashboardOperationalPerformanceAmountRow array{
 *   period_key:string,
 *   period_label:string,
 *   amount_rupiah:int
 * }
 */
final class DashboardOperationalPerformancePeriodAmountMerger
{
    /**
     * @param array<string, DashboardOperationalPerformancePeriodRow> $rowsByKey
     * @param list<DashboardOperationalPerformanceAmountRow> $amountRows
     * @param 'cash_in_rupiah'|'refund_rupiah'|'external_purchase_cost_rupiah'|'store_stock_cogs_rupiah'|'operational_expense_rupiah'|'payroll_disbursement_rupiah'|'employee_debt_cash_out_rupiah' $field
     */
    public function merge(array &$rowsByKey, array $amountRows, string $field): void
    {
        foreach ($amountRows as $amountRow) {
            $periodKey = $amountRow['period_key'];

            if (! isset($rowsByKey[$periodKey])) {
                continue;
            }

            $rowsByKey[$periodKey][$field] = $amountRow['amount_rupiah'];
        }
    }
}
