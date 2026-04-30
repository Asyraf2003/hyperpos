<?php

declare(strict_types=1);

namespace App\Adapters\Out\Reporting\Queries;

use Illuminate\Support\Facades\DB;

final class DashboardTopSellingProductQuery
{
    public function rows(
        string $fromTransactionDate,
        string $toTransactionDate,
        int $limit,
    ): array {
        $reversalRows = DB::table('inventory_movements')
            ->selectRaw('source_id, COALESCE(SUM(qty_delta), 0) as reversal_qty')
            ->where('source_type', 'work_item_store_stock_line_reversal')
            ->where('qty_delta', '>', 0)
            ->groupBy('source_id');

        $reversalQtyExpression = 'COALESCE(reversal_movements.reversal_qty, 0)';
        $netQtyExpression = "CASE WHEN stock_lines.qty <= {$reversalQtyExpression} THEN 0 ELSE stock_lines.qty - {$reversalQtyExpression} END";
        $netRevenueExpression = "CASE WHEN stock_lines.qty <= 0 THEN 0 WHEN stock_lines.qty <= {$reversalQtyExpression} THEN 0 ELSE ROUND((stock_lines.line_total_rupiah * (stock_lines.qty - {$reversalQtyExpression})) / stock_lines.qty, 0) END";

        return DB::table('work_item_store_stock_lines as stock_lines')
            ->join('work_items', 'work_items.id', '=', 'stock_lines.work_item_id')
            ->join('notes', 'notes.id', '=', 'work_items.note_id')
            ->leftJoin('products', 'products.id', '=', 'stock_lines.product_id')
            ->leftJoinSub(
                $reversalRows,
                'reversal_movements',
                static function ($join): void {
                    $join->on('reversal_movements.source_id', '=', 'stock_lines.id');
                }
            )
            ->whereBetween('notes.transaction_date', [$fromTransactionDate, $toTransactionDate])
            ->selectRaw(
                "stock_lines.product_id as product_id,
                 MAX(products.kode_barang) as kode_barang,
                 COALESCE(MAX(products.nama_barang), stock_lines.product_id) as nama_barang,
                 COALESCE(SUM({$netQtyExpression}), 0) as sold_qty,
                 COALESCE(SUM({$netRevenueExpression}), 0) as gross_revenue_rupiah"
            )
            ->groupBy('stock_lines.product_id')
            ->havingRaw("COALESCE(SUM({$netQtyExpression}), 0) > 0")
            ->orderByDesc('sold_qty')
            ->orderByDesc('gross_revenue_rupiah')
            ->orderBy('nama_barang')
            ->limit(max(1, $limit))
            ->get()
            ->map(static fn (object $row): array => [
                'product_id' => (string) $row->product_id,
                'kode_barang' => $row->kode_barang !== null ? (string) $row->kode_barang : null,
                'nama_barang' => (string) $row->nama_barang,
                'sold_qty' => (int) $row->sold_qty,
                'gross_revenue_rupiah' => (int) $row->gross_revenue_rupiah,
            ])
            ->all();
    }
}
