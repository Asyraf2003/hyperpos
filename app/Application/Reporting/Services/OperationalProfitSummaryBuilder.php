<?php

declare(strict_types=1);

namespace App\Application\Reporting\Services;

use App\Application\Reporting\DTO\OperationalProfitSummaryRow;

final class OperationalProfitSummaryBuilder
{
    public function build(array $row): OperationalProfitSummaryRow
    {
        return new OperationalProfitSummaryRow(
            $row['from_date'],
            $row['to_date'],
            $row['gross_revenue_rupiah'],
            $row['refunded_rupiah'],
            $row['net_revenue_rupiah'],
            $row['external_purchase_cost_rupiah'],
            $row['store_stock_cogs_rupiah'],
            $row['direct_cost_rupiah'],
            $row['gross_profit_rupiah'],
            $row['operational_expense_rupiah'],
            $row['payroll_disbursement_rupiah'],
            $row['net_operational_profit_rupiah'],
        );
    }
}
