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
            $row['cash_in_rupiah'],
            $row['refunded_rupiah'],
            $row['external_purchase_cost_rupiah'],
            $row['store_stock_cogs_rupiah'],
            $row['product_purchase_cost_rupiah'],
            $row['operational_expense_rupiah'],
            $row['payroll_disbursement_rupiah'],
            $row['employee_debt_cash_out_rupiah'],
            $row['cash_operational_profit_rupiah'],
        );
    }
}
