<?php

declare(strict_types=1);

namespace App\Adapters\Out\ProductCatalog;

use App\Adapters\Out\ProductCatalog\Concerns\TranslatesProductWriteConflicts;
use App\Core\ProductCatalog\Product\Product;
use App\Ports\Out\ProductCatalog\ProductWriterPort;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

final class DatabaseProductWriterAdapter implements ProductWriterPort
{
    use TranslatesProductWriteConflicts;

    public function create(Product $product): void
    {
        try {
            DB::table('products')->insert($this->toRecord($product));
        } catch (QueryException $e) {
            throw $this->translateProductWriteConflict($e);
        }
    }

    public function update(Product $product): void
    {
        try {
            DB::table('products')
                ->where('id', $product->id())
                ->update($this->toRecord($product));
        } catch (QueryException $e) {
            throw $this->translateProductWriteConflict($e);
        }
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
