<?php

declare(strict_types=1);

namespace App\Adapters\Out\ProductCatalog;

use App\Core\ProductCatalog\Product\Product;
use App\Core\Shared\ValueObjects\Money;
use App\Ports\Out\ProductCatalog\ProductReaderPort;
use Illuminate\Support\Facades\DB;

final class DatabaseProductReaderAdapter implements ProductReaderPort
{
    public function getById(string $productId): ?Product
    {
        $row = DB::table('products')
            ->select(['id', 'kode_barang', 'nama_barang', 'merek', 'ukuran', 'harga_jual'])
            ->where('id', $productId)
            ->first();

        if ($row === null) {
            return null;
        }

        return Product::rehydrate(
            (string) $row->id,
            $row->kode_barang !== null ? (string) $row->kode_barang : null,
            (string) $row->nama_barang,
            (string) $row->merek,
            $row->ukuran !== null ? (int) $row->ukuran : null,
            Money::fromInt((int) $row->harga_jual),
        );
    }
}
