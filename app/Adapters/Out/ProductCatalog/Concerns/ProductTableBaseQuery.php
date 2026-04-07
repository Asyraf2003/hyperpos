<?php

declare(strict_types=1);

namespace App\Adapters\Out\ProductCatalog\Concerns;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

trait ProductTableBaseQuery
{
    private function baseTableQuery(): Builder
    {
        return DB::table('products')
            ->leftJoin('product_inventory', 'product_inventory.product_id', '=', 'products.id')
            ->whereNull('products.deleted_at')
            ->select([
                'products.id',
                'products.kode_barang',
                'products.nama_barang',
                'products.merek',
                'products.ukuran',
                'products.harga_jual',
            ])
            ->selectRaw('COALESCE(product_inventory.qty_on_hand, 0) as stok_saat_ini');
    }
}
