<?php

declare(strict_types=1);

namespace App\Ports\Out\Reporting;

interface OperationalProfitReportingSourceReaderPort
{
    /**
     * @return array{
     *   from_date:string,
     *   to_date:string,
     *   gross_revenue_rupiah:int,
     *   refunded_rupiah:int,
     *   net_revenue_rupiah:int,
     *   external_purchase_cost_rupiah:int,
     *   store_stock_cogs_rupiah:int,
     *   direct_cost_rupiah:int,
     *   gross_profit_rupiah:int,
     *   operational_expense_rupiah:int,
     *   payroll_disbursement_rupiah:int,
     *   net_operational_profit_rupiah:int
     * }
     */
    public function getOperationalProfitSummary(
        string $fromDate,
        string $toDate,
    ): array;

    /**
     * @return array{
     *   gross_revenue_rupiah:int,
     *   refunded_rupiah:int,
     *   net_revenue_rupiah:int,
     *   external_purchase_cost_rupiah:int,
     *   store_stock_cogs_rupiah:int,
     *   direct_cost_rupiah:int,
     *   gross_profit_rupiah:int,
     *   operational_expense_rupiah:int,
     *   payroll_disbursement_rupiah:int,
     *   net_operational_profit_rupiah:int
     * }
     */
    public function getOperationalProfitReconciliation(
        string $fromDate,
        string $toDate,
    ): array;
}
