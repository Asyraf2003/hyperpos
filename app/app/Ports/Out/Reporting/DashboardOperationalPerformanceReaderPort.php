<?php

declare(strict_types=1);

namespace App\Ports\Out\Reporting;

/**
 * @phpstan-type DashboardOperationalPerformancePeriodRow array{
 *   period_key:string,
 *   period_label:string,
 *   cash_in_rupiah:int,
 *   refund_rupiah:int,
 *   external_purchase_cost_rupiah:int,
 *   store_stock_cogs_rupiah:int,
 *   operational_expense_rupiah:int,
 *   payroll_disbursement_rupiah:int,
 *   employee_debt_cash_out_rupiah:int,
 *   operational_profit_rupiah:int
 * }
 */
interface DashboardOperationalPerformanceReaderPort
{
    /**
     * @return list<DashboardOperationalPerformancePeriodRow>
     */
    public function getOperationalPerformancePeriodRows(
        string $fromDate,
        string $toDate,
    ): array;
}
