<?php

declare(strict_types=1);

namespace App\Adapters\Out\Reporting\Queries;

use Illuminate\Support\Facades\DB;

final class OperationalProfitMetricsQuery
{
    public function summary(string $fromDate, string $toDate): array
    {
        $cashIn = $this->cashIn($fromDate, $toDate);
        $refund = $this->refund($fromDate, $toDate);
        $externalPurchase = $this->externalPurchaseCost($fromDate, $toDate);
        $storeStockCogs = $this->storeStockCogs($fromDate, $toDate);
        $productPurchaseCost = $externalPurchase + $storeStockCogs;
        $operationalExpense = $this->operationalExpense($fromDate, $toDate);
        $payroll = $this->payrollDisbursement($fromDate, $toDate);
        $employeeDebtCashOut = $this->employeeDebtCashOut($fromDate, $toDate);

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

    private function cashIn(string $fromDate, string $toDate): int
    {
        return (int) (DB::table('customer_payments')
            ->whereBetween(DB::raw('DATE(paid_at)'), [$fromDate, $toDate])
            ->sum('amount_rupiah') ?? 0);
    }

    private function refund(string $fromDate, string $toDate): int
    {
        return (int) (DB::table('customer_refunds')
            ->whereBetween(DB::raw('DATE(refunded_at)'), [$fromDate, $toDate])
            ->sum('amount_rupiah') ?? 0);
    }

    private function externalPurchaseCost(string $fromDate, string $toDate): int
    {
        return (int) (DB::table('work_item_external_purchase_lines')
            ->join('work_items', 'work_items.id', '=', 'work_item_external_purchase_lines.work_item_id')
            ->join('notes', 'notes.id', '=', 'work_items.note_id')
            ->whereBetween('notes.transaction_date', [$fromDate, $toDate])
            ->sum('work_item_external_purchase_lines.line_total_rupiah') ?? 0);
    }

    private function storeStockCogs(string $fromDate, string $toDate): int
    {
        return (int) (DB::table('inventory_movements')
            ->where('movement_type', 'stock_out')
            ->where('source_type', 'work_item_store_stock_line')
            ->whereBetween('tanggal_mutasi', [$fromDate, $toDate])
            ->sum(DB::raw('ABS(total_cost_rupiah)')) ?? 0);
    }

    private function operationalExpense(string $fromDate, string $toDate): int
    {
        return (int) (DB::table('operational_expenses')
            ->whereNull('deleted_at')
            ->whereBetween('expense_date', [$fromDate, $toDate])
            ->sum('amount_rupiah') ?? 0);
    }

    private function payrollDisbursement(string $fromDate, string $toDate): int
    {
        return (int) (DB::table('payroll_disbursements')
            ->leftJoin(
                'payroll_disbursement_reversals',
                'payroll_disbursements.id',
                '=',
                'payroll_disbursement_reversals.payroll_disbursement_id'
            )
            ->whereNull('payroll_disbursement_reversals.id')
            ->whereBetween(DB::raw('DATE(payroll_disbursements.disbursement_date)'), [$fromDate, $toDate])
            ->sum('payroll_disbursements.amount') ?? 0);
    }

    private function employeeDebtCashOut(string $fromDate, string $toDate): int
    {
        return (int) (DB::table('employee_debts')
            ->whereBetween(DB::raw('DATE(employee_debts.created_at)'), [$fromDate, $toDate])
            ->sum('employee_debts.total_debt') ?? 0);
    }
}
