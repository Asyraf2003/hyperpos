<?php

declare(strict_types=1);

namespace App\Adapters\Out\Reporting\Queries;

use Illuminate\Support\Facades\DB;

final class OperationalProfitMetricsQuery
{
    public function summary(string $fromDate, string $toDate): array
    {
        $grossRevenue = $this->grossRevenue($fromDate, $toDate);
        $refunded = $this->refunded($fromDate, $toDate);
        $externalPurchaseCost = $this->externalPurchaseCost($fromDate, $toDate);
        $storeStockCogs = $this->storeStockCogs($fromDate, $toDate);
        $operationalExpense = $this->operationalExpense($fromDate, $toDate);
        $payroll = $this->payrollDisbursement($fromDate, $toDate);
        $netRevenue = $grossRevenue - $refunded;
        $directCost = $externalPurchaseCost + $storeStockCogs;
        $grossProfit = $netRevenue - $directCost;

        return [
            'from_date' => $fromDate,
            'to_date' => $toDate,
            'gross_revenue_rupiah' => $grossRevenue,
            'refunded_rupiah' => $refunded,
            'net_revenue_rupiah' => $netRevenue,
            'external_purchase_cost_rupiah' => $externalPurchaseCost,
            'store_stock_cogs_rupiah' => $storeStockCogs,
            'direct_cost_rupiah' => $directCost,
            'gross_profit_rupiah' => $grossProfit,
            'operational_expense_rupiah' => $operationalExpense,
            'payroll_disbursement_rupiah' => $payroll,
            'net_operational_profit_rupiah' => $grossProfit - $operationalExpense - $payroll,
        ];
    }

    private function grossRevenue(string $fromDate, string $toDate): int
    {
        return (int) (DB::table('notes')->whereBetween('transaction_date', [$fromDate, $toDate])->sum('total_rupiah') ?? 0);
    }

    private function refunded(string $fromDate, string $toDate): int
    {
        return (int) (DB::table('customer_refunds')->whereBetween(DB::raw('DATE(refunded_at)'), [$fromDate, $toDate])->sum('amount_rupiah') ?? 0);
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
}
