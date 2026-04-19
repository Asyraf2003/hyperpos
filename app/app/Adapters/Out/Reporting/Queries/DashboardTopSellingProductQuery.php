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
        return DB::table('work_item_store_stock_lines as stock_lines')
            ->join('work_items', 'work_items.id', '=', 'stock_lines.work_item_id')
            ->join('notes', 'notes.id', '=', 'work_items.note_id')
            ->leftJoin('products', 'products.id', '=', 'stock_lines.product_id')
            ->whereBetween('notes.transaction_date', [$fromTransactionDate, $toTransactionDate])
            ->selectRaw(
                'stock_lines.product_id as product_id,
                 MAX(products.kode_barang) as kode_barang,
                 COALESCE(MAX(products.nama_barang), stock_lines.product_id) as nama_barang,
                 COALESCE(SUM(stock_lines.qty), 0) as sold_qty,
                 COALESCE(SUM(stock_lines.line_total_rupiah), 0) as gross_revenue_rupiah'
            )
            ->groupBy('stock_lines.product_id')
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
