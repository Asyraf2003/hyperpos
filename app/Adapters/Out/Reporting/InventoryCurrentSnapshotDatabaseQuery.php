<?php

declare(strict_types=1);

namespace App\Adapters\Out\Reporting;

use Illuminate\Support\Facades\DB;

final class InventoryCurrentSnapshotDatabaseQuery
{
    public static function get(): array
    {
        return DB::table('products')
            ->whereNull('products.deleted_at')
            ->leftJoin('product_inventory', 'product_inventory.product_id', '=', 'products.id')
            ->leftJoin('product_inventory_costing', 'product_inventory_costing.product_id', '=', 'products.id')
            ->where(static function ($query): void {
                $query
                    ->whereNotNull('product_inventory.product_id')
                    ->orWhereNotNull('product_inventory_costing.product_id');
            })
            ->orderBy('products.id')
            ->get(self::columns())
            ->map(static fn (object $row): array => InventoryCurrentSnapshotRowMapper::map($row))
            ->all();
    }

    private static function columns(): array
    {
        return [
            'products.id as product_id',
            'products.kode_barang',
            'products.nama_barang',
            'products.merek',
            'products.ukuran',
            'products.reorder_point_qty',
            'products.critical_threshold_qty',
            DB::raw('COALESCE(product_inventory.qty_on_hand, 0) as current_qty_on_hand'),
            DB::raw('COALESCE(product_inventory_costing.avg_cost_rupiah, 0) as current_avg_cost_rupiah'),
            DB::raw('COALESCE(product_inventory_costing.inventory_value_rupiah, 0) as current_inventory_value_rupiah'),
        ];
    }
}
