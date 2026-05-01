<?php

declare(strict_types=1);

namespace App\Application\Reporting\UseCases\Concerns;

trait BuildsAdminDashboardOverviewContext
{
    private static function ledgerActivity(
        array $transactionSummary,
        array $inventorySummary,
        array $monthCash,
    ): array {
        $cashIn = (int) ($monthCash['total_in_rupiah'] ?? 0);
        $cashOut = (int) ($monthCash['total_out_rupiah'] ?? 0);
        $stockOutQty = (int) ($inventorySummary['period_sale_out_qty'] ?? 0);
        $stockReversalQty = (int) ($inventorySummary['period_refund_reversal_qty'] ?? 0);

        return [
            'gross_transaction_rupiah' => (int) ($transactionSummary['gross_transaction_rupiah'] ?? 0),
            'cash_in_before_refund_rupiah' => $cashIn,
            'cash_refund_out_rupiah' => $cashOut,
            'net_cash_flow_rupiah' => $cashIn - $cashOut,
            'stock_out_qty_before_reversal' => $stockOutQty,
            'stock_reversal_qty' => $stockReversalQty,
            'net_stock_out_qty' => $stockOutQty - $stockReversalQty,
            'is_cash_fully_refunded_period' => $cashIn > 0 && $cashOut >= $cashIn,
        ];
    }

    private static function position(
        array $inventorySummary,
        array $transactionSummary,
        array $supplierPayableSummary,
        array $employeeDebtSummary,
        array $operationalExpenseSummary,
    ): array {
        return [
            'inventory_value_rupiah' => (int) ($inventorySummary['total_inventory_value_rupiah'] ?? 0),
            'transaction_outstanding_rupiah' => (int) ($transactionSummary['outstanding_rupiah'] ?? 0),
            'supplier_outstanding_rupiah' => (int) ($supplierPayableSummary['outstanding_rupiah'] ?? 0),
            'employee_debt_remaining_rupiah' => (int) ($employeeDebtSummary['total_remaining_balance'] ?? 0),
            'monthly_refunded_rupiah' => (int) ($transactionSummary['refunded_rupiah'] ?? 0),
            'monthly_operational_expense_rupiah' => (int) ($operationalExpenseSummary['total_amount_rupiah'] ?? 0),
        ];
    }
}
