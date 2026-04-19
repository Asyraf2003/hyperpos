<?php

declare(strict_types=1);

namespace App\Adapters\Out\Reporting\Queries;

use App\Adapters\Out\Reporting\Queries\OperationalProfit\CashFlowMetricQuery;
use App\Adapters\Out\Reporting\Queries\OperationalProfit\OperatingCostMetricQuery;
use App\Adapters\Out\Reporting\Queries\OperationalProfit\ProductCostMetricQuery;

final class OperationalProfitMetricsQuery
{
    public function __construct(
        private readonly CashFlowMetricQuery $cashFlow,
        private readonly ProductCostMetricQuery $productCosts,
        private readonly OperatingCostMetricQuery $operatingCosts,
    ) {
    }

    public function summary(string $fromDate, string $toDate): array
    {
        $cashIn = $this->cashFlow->cashIn($fromDate, $toDate);
        $refund = $this->cashFlow->refund($fromDate, $toDate);
        $externalPurchase = $this->productCosts->externalPurchaseCost($fromDate, $toDate);
        $storeStockCogs = $this->productCosts->storeStockCogs($fromDate, $toDate);
        $productPurchaseCost = $externalPurchase + $storeStockCogs;
        $operationalExpense = $this->operatingCosts->operationalExpense($fromDate, $toDate);
        $payroll = $this->operatingCosts->payrollDisbursement($fromDate, $toDate);
        $employeeDebtCashOut = $this->operatingCosts->employeeDebtCashOut($fromDate, $toDate);

        return [
            'from_date' => $fromDate,
            'to_date' => $toDate,
            'cash_in_rupiah' => $cashIn,
            'refunded_rupiah' => $refund,
            'external_purchase_cost_rupiah' => $externalPurchase,
            'store_stock_cogs_rupiah' => $storeStockCogs,
            'product_purchase_cost_rupiah' => $productPurchaseCost,
            'operational_expense_rupiah' => $operationalExpense,
            'payroll_disbursement_rupiah' => $payroll,
            'employee_debt_cash_out_rupiah' => $employeeDebtCashOut,
            'cash_operational_profit_rupiah' => $cashIn
                - $refund
                - $productPurchaseCost
                - $operationalExpense
                - $payroll
                - $employeeDebtCashOut,
        ];
    }
}
