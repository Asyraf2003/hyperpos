<?php

declare(strict_types=1);

namespace App\Adapters\Out\Reporting\Queries\OperationalProfit;

use Illuminate\Support\Facades\DB;

final class ProductCostMetricQuery
{
    public function externalPurchaseCost(string $fromDate, string $toDate): int
    {
        return (int) (DB::table('work_item_external_purchase_lines')
            ->join('work_items', 'work_items.id', '=', 'work_item_external_purchase_lines.work_item_id')
            ->join('notes', 'notes.id', '=', 'work_items.note_id')
            ->whereBetween('notes.transaction_date', [$fromDate, $toDate])
            ->sum('work_item_external_purchase_lines.line_total_rupiah') ?? 0);
    }

    public function storeStockCogs(string $fromDate, string $toDate): int
    {
        $issued = (int) (DB::table('inventory_movements')
            ->where('movement_type', 'stock_out')
            ->where('source_type', 'work_item_store_stock_line')
            ->whereBetween('tanggal_mutasi', [$fromDate, $toDate])
            ->sum(DB::raw('ABS(total_cost_rupiah)')) ?? 0);

        $returned = (int) (DB::table('inventory_movements')
            ->where('movement_type', 'stock_in')
            ->where('source_type', 'work_item_store_stock_line_reversal')
            ->whereBetween('tanggal_mutasi', [$fromDate, $toDate])
            ->sum('total_cost_rupiah') ?? 0);

        return max($issued - $returned, 0);
    }
}
