<?php

declare(strict_types=1);

namespace App\Adapters\Out\ProductCatalog;

use App\Core\ProductCatalog\Product\Product;
use App\Ports\Out\ProductCatalog\ProductWriterPort;
use Illuminate\Support\Facades\DB;

final class DatabaseProductWriterAdapter implements ProductWriterPort
{
    public function create(Product $product): void
    {
        DB::table('products')->insert($this->toRecord($product));
    }

    public function update(Product $product): void
    {
        DB::table('products')
            ->where('id', $product->id())
            ->update($this->toRecord($product));
    }

    /**
     * @return array<string, string|int|null>
     */
    private function toRecord(Product $product): array
    {
        return [
            'id' => $product->id(),
            'kode_barang' => $product->kodeBarang(),
            'nama_barang' => $product->namaBarang(),
            'merek' => $product->merek(),
            'ukuran' => $product->ukuran(),
            'harga_jual' => $product->hargaJual()->amount(),
        ];
    }
}
