<?php

declare(strict_types=1);

namespace App\Application\ProductCatalog\UseCases;

use App\Core\ProductCatalog\Product\Product;

final class UpdateProductSuccessPayloadBuilder
{
    /**
     * @return array<string, mixed>
     */
    public function build(Product $product): array
    {
        return [
            'id' => $product->id(),
            'kode_barang' => $product->kodeBarang(),
            'nama_barang' => $product->namaBarang(),
            'merek' => $product->merek(),
            'ukuran' => $product->ukuran(),
            'harga_jual' => $product->hargaJual()->amount(),
            'reorder_point_qty' => $product->reorderPointQty(),
            'critical_threshold_qty' => $product->criticalThresholdQty(),
        ];
    }
}
