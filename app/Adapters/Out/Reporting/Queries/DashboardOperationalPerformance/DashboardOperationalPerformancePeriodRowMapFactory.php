<?php

declare(strict_types=1);

namespace App\Adapters\Out\Reporting\Queries\DashboardOperationalPerformance;

use App\Ports\Out\Reporting\DashboardOperationalPerformanceReaderPort;
use Carbon\CarbonImmutable;

/**
 * @phpstan-import-type DashboardOperationalPerformancePeriodRow from DashboardOperationalPerformanceReaderPort
 */
final class DashboardOperationalPerformancePeriodRowMapFactory
{
    /**
     * @return array<string, DashboardOperationalPerformancePeriodRow>
     */
    public function create(string $fromDate, string $toDate): array
    {
        $cursor = CarbonImmutable::parse($fromDate);
        $end = CarbonImmutable::parse($toDate);
        $rowsByKey = [];

        while ($cursor->lte($end)) {
            $date = $cursor->toDateString();

            $rowsByKey[$date] = [
                'period_key' => $date,
                'period_label' => $date,
                'cash_in_rupiah' => 0,
                'refund_rupiah' => 0,
                'external_purchase_cost_rupiah' => 0,
                'store_stock_cogs_rupiah' => 0,
                'operational_expense_rupiah' => 0,
                'payroll_disbursement_rupiah' => 0,
                'employee_debt_cash_out_rupiah' => 0,
                'operational_profit_rupiah' => 0,
            ];

            $cursor = $cursor->addDay();
        }

        return $rowsByKey;
    }
}
