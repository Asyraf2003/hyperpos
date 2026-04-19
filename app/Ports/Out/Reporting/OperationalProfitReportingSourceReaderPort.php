<?php

declare(strict_types=1);

namespace App\Ports\Out\Reporting;

interface OperationalProfitReportingSourceReaderPort
{
    /**
     * @return array{
     *   from_date:string,
     *   to_date:string,
     *   cash_in_rupiah:int,
     *   refunded_rupiah:int,
     *   external_purchase_cost_rupiah:int,
     *   store_stock_cogs_rupiah:int,
     *   product_purchase_cost_rupiah:int,
     *   operational_expense_rupiah:int,
     *   payroll_disbursement_rupiah:int,
     *   employee_debt_cash_out_rupiah:int,
     *   cash_operational_profit_rupiah:int
     * }
     */
    public function getOperationalProfitSummary(
        string $fromDate,
        string $toDate,
    ): array;

    /**
     * @return array{
     *   cash_in_rupiah:int,
     *   refunded_rupiah:int,
     *   external_purchase_cost_rupiah:int,
     *   store_stock_cogs_rupiah:int,
     *   product_purchase_cost_rupiah:int,
     *   operational_expense_rupiah:int,
     *   payroll_disbursement_rupiah:int,
     *   employee_debt_cash_out_rupiah:int,
     *   cash_operational_profit_rupiah:int
     * }
     */
    public function getOperationalProfitReconciliation(
        string $fromDate,
        string $toDate,
    ): array;
}
