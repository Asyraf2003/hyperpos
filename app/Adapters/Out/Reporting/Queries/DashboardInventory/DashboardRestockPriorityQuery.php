<?php

declare(strict_types=1);

namespace App\Adapters\Out\Reporting\Queries\DashboardInventory;

use Illuminate\Support\Facades\DB;

final class DashboardRestockPriorityQuery
{
    /**
     * @return list<array{
     *   product_id:string,
     *   kode_barang:string|null,
     *   nama_barang:string,
     *   current_qty_on_hand:int,
     *   reorder_point_qty:int|null,
     *   critical_threshold_qty:int|null,
     *   status:string,
     *   status_label:string
     * }>
     */
    public function get(int $limit): array
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
            ->whereNotNull('products.reorder_point_qty')
            ->whereNotNull('products.critical_threshold_qty')
            ->where(static function ($query): void {
                $query
                    ->whereRaw('COALESCE(product_inventory.qty_on_hand, 0) <= products.critical_threshold_qty')
                    ->orWhereRaw('COALESCE(product_inventory.qty_on_hand, 0) <= products.reorder_point_qty');
            })
            ->orderByRaw(
                'CASE ' .
                'WHEN COALESCE(product_inventory.qty_on_hand, 0) <= products.critical_threshold_qty THEN 0 ' .
                'ELSE 1 END ASC'
            )
            ->orderByRaw('COALESCE(product_inventory.qty_on_hand, 0) ASC')
            ->orderBy('products.id')
            ->limit($limit)
            ->get([
                'products.id as product_id',
                'products.kode_barang',
                'products.nama_barang',
                'products.reorder_point_qty',
                'products.critical_threshold_qty',
                DB::raw('COALESCE(product_inventory.qty_on_hand, 0) as current_qty_on_hand'),
            ])
            ->map(static fn (object $row): array => [
                'product_id' => (string) $row->product_id,
                'kode_barang' => $row->kode_barang === null ? null : (string) $row->kode_barang,
                'nama_barang' => (string) ($row->nama_barang ?? '-'),
                'current_qty_on_hand' => (int) $row->current_qty_on_hand,
                'reorder_point_qty' => $row->reorder_point_qty === null ? null : (int) $row->reorder_point_qty,
                'critical_threshold_qty' => $row->critical_threshold_qty === null ? null : (int) $row->critical_threshold_qty,
                'status' => self::status($row),
                'status_label' => self::status($row) === 'critical' ? 'Kritis' : 'Mulai Perlu Restok',
            ])
            ->all();
    }

    private static function status(object $row): string
    {
        return (int) $row->current_qty_on_hand <= (int) $row->critical_threshold_qty
            ? 'critical'
            : 'low';
    }
}
