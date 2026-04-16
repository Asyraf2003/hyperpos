<?php

declare(strict_types=1);

namespace App\Application\Reporting\UseCases;

final class AdminDashboardOverviewPayload
{
    public static function fromSources(
        array $transactionSummary,
        array $inventorySummary,
        array $operationalProfitRow,
        array $supplierPayableSummary,
        array $employeeDebtSummary,
        array $operationalExpenseSummary,
        array $todayCash,
        array $monthCash,
        array $topSellingRows,
        array $restockPriorityRows,
    ): array {
        return [
            'hero' => self::hero($transactionSummary),
            'stats' => self::stats($inventorySummary, $todayCash, $operationalProfitRow),
            'finance' => self::finance($monthCash, $operationalProfitRow),
            'top_selling_rows' => self::topSellingRows($topSellingRows),
            'restock_priority_rows' => $restockPriorityRows,
            'position' => self::position(
                $inventorySummary,
                $transactionSummary,
                $supplierPayableSummary,
                $employeeDebtSummary,
                $operationalExpenseSummary,
            ),
        ];
    }

    private static function hero(array $transactionSummary): array
    {
        return [
            'monthly_gross_transaction_rupiah' => (int) ($transactionSummary['gross_transaction_rupiah'] ?? 0),
            'monthly_net_cash_collected_rupiah' => (int) ($transactionSummary['net_cash_collected_rupiah'] ?? 0),
            'monthly_outstanding_rupiah' => (int) ($transactionSummary['outstanding_rupiah'] ?? 0),
        ];
    }

    private static function stats(array $inventorySummary, array $todayCash, array $operationalProfitRow): array
    {
        return [
            'total_qty_on_hand' => (int) ($inventorySummary['total_qty_on_hand'] ?? 0),
            'total_inventory_value_rupiah' => (int) ($inventorySummary['total_inventory_value_rupiah'] ?? 0),
            'stock_safe_product_rows' => (int) ($inventorySummary['stock_safe_product_rows'] ?? 0),
            'stock_low_product_rows' => (int) ($inventorySummary['stock_low_product_rows'] ?? 0),
            'stock_critical_product_rows' => (int) ($inventorySummary['stock_critical_product_rows'] ?? 0),
            'stock_unconfigured_product_rows' => (int) ($inventorySummary['stock_unconfigured_product_rows'] ?? 0),
            'daily_cash_in_rupiah' => (int) ($todayCash['total_in_rupiah'] ?? 0),
            'monthly_cash_operational_profit_rupiah' => (int) ($operationalProfitRow['cash_operational_profit_rupiah'] ?? 0),
        ];
    }

    private static function finance(array $monthCash, array $operationalProfitRow): array
    {
        return [
            'monthly_cash_in_rupiah' => (int) ($monthCash['total_in_rupiah'] ?? 0),
            'monthly_cash_out_rupiah' => (int) ($monthCash['total_out_rupiah'] ?? 0),
            'monthly_cash_operational_profit_rupiah' => (int) ($operationalProfitRow['cash_operational_profit_rupiah'] ?? 0),
            'monthly_net_cash_flow_rupiah' => (int) (($monthCash['total_in_rupiah'] ?? 0) - ($monthCash['total_out_rupiah'] ?? 0)),
        ];
    }

    private static function topSellingRows(array $rows): array
    {
        return array_values(array_map(
            static fn (array $row): array => [
                'product_id' => (string) ($row['product_id'] ?? ''),
                'kode_barang' => array_key_exists('kode_barang', $row) && $row['kode_barang'] !== null
                    ? (string) $row['kode_barang']
                    : null,
                'nama_barang' => (string) ($row['nama_barang'] ?? '-'),
                'sold_qty' => (int) ($row['sold_qty'] ?? 0),
                'gross_revenue_rupiah' => (int) ($row['gross_revenue_rupiah'] ?? 0),
            ],
            $rows
        ));
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
