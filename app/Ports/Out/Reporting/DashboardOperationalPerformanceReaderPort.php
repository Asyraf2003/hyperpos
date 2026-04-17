<?php

declare(strict_types=1);

namespace App\Ports\Out\Reporting;

interface DashboardOperationalPerformanceReaderPort
{
    /**
     * @return list<array{
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
     * }>
     */
    public function getOperationalPerformancePeriodRows(
        string $fromDate,
        string $toDate,
    ): array;
}
