<?php

declare(strict_types=1);

namespace App\Adapters\Out\Reporting;

use App\Adapters\Out\Reporting\Queries\OperationalProfitMetricsQuery;
use App\Ports\Out\Reporting\OperationalProfitReportingSourceReaderPort;

final class DatabaseOperationalProfitReportingSourceReaderAdapter implements OperationalProfitReportingSourceReaderPort
{
    public function __construct(
        private readonly OperationalProfitMetricsQuery $metricsQuery,
    ) {
    }

    public function getOperationalProfitSummary(string $fromDate, string $toDate): array
    {
        return $this->metricsQuery->summary($fromDate, $toDate);
    }

    public function getOperationalProfitReconciliation(string $fromDate, string $toDate): array
    {
        $summary = $this->metricsQuery->summary($fromDate, $toDate);

        return [
            'cash_in_rupiah' => $summary['cash_in_rupiah'],
            'refunded_rupiah' => $summary['refunded_rupiah'],
            'external_purchase_cost_rupiah' => $summary['external_purchase_cost_rupiah'],
            'store_stock_cogs_rupiah' => $summary['store_stock_cogs_rupiah'],
            'product_purchase_cost_rupiah' => $summary['product_purchase_cost_rupiah'],
            'operational_expense_rupiah' => $summary['operational_expense_rupiah'],
            'payroll_disbursement_rupiah' => $summary['payroll_disbursement_rupiah'],
            'employee_debt_cash_out_rupiah' => $summary['employee_debt_cash_out_rupiah'],
            'cash_operational_profit_rupiah' => $summary['cash_operational_profit_rupiah'],
        ];
    }
}
