<?php

declare(strict_types=1);

namespace App\Adapters\Out\Reporting;

use App\Ports\Out\Reporting\OperationalProfitReportingSourceReaderPort;
use Illuminate\Support\Facades\DB;

final class DatabaseOperationalProfitReportingSourceReaderAdapter implements OperationalProfitReportingSourceReaderPort
{
    public function getOperationalProfitSummary(
        string $fromDate,
        string $toDate,
    ): array {
        $grossRevenue = $this->grossRevenue($fromDate, $toDate);
        $refunded = $this->refunded($fromDate, $toDate);
        $externalPurchaseCost = $this->externalPurchaseCost($fromDate, $toDate);
        $storeStockCogs = $this->storeStockCogs($fromDate, $toDate);
        $operationalExpense = $this->operationalExpense($fromDate, $toDate);
        $payroll = $this->payrollDisbursement($fromDate, $toDate);

        $netRevenue = $grossRevenue - $refunded;
        $directCost = $externalPurchaseCost + $storeStockCogs;
        $grossProfit = $netRevenue - $directCost;
        $netOperationalProfit = $grossProfit - $operationalExpense - $payroll;

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
            'net_operational_profit_rupiah' => $netOperationalProfit,
        ];
    }

    public function getOperationalProfitReconciliation(
        string $fromDate,
        string $toDate,
    ): array {
        $summary = $this->getOperationalProfitSummary($fromDate, $toDate);

        return [
            'gross_revenue_rupiah' => $summary['gross_revenue_rupiah'],
            'refunded_rupiah' => $summary['refunded_rupiah'],
            'net_revenue_rupiah' => $summary['net_revenue_rupiah'],
            'external_purchase_cost_rupiah' => $summary['external_purchase_cost_rupiah'],
            'store_stock_cogs_rupiah' => $summary['store_stock_cogs_rupiah'],
            'direct_cost_rupiah' => $summary['direct_cost_rupiah'],
            'gross_profit_rupiah' => $summary['gross_profit_rupiah'],
            'operational_expense_rupiah' => $summary['operational_expense_rupiah'],
            'payroll_disbursement_rupiah' => $summary['payroll_disbursement_rupiah'],
            'net_operational_profit_rupiah' => $summary['net_operational_profit_rupiah'],
        ];
    }

    private function grossRevenue(string $fromDate, string $toDate): int
    {
        return (int) (DB::table('notes')
            ->whereBetween('transaction_date', [$fromDate, $toDate])
            ->sum('total_rupiah') ?? 0);
    }

    private function refunded(string $fromDate, string $toDate): int
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
            ->where('status', 'posted')
            ->whereBetween('expense_date', [$fromDate, $toDate])
            ->sum('amount_rupiah') ?? 0);
    }

    private function payrollDisbursement(string $fromDate, string $toDate): int
    {
        return (int) (DB::table('payroll_disbursements')
            ->whereBetween(DB::raw('DATE(disbursement_date)'), [$fromDate, $toDate])
            ->sum('amount') ?? 0);
    }
}
