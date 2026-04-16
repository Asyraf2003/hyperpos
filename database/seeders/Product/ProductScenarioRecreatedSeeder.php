<?php

declare(strict_types=1);

namespace Database\Seeders\Product;

use App\Application\ProductCatalog\UseCases\CreateProductHandler;
use App\Application\ProductCatalog\UseCases\SoftDeleteProductHandler;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;

final class ProductScenarioRecreatedSeeder extends Seeder
{
    public function run(
        CreateProductHandler $createHandler,
        SoftDeleteProductHandler $deleteHandler,
    ): void {
        $items = ProductSeedCatalog::all()['recreated_after_delete'];

        foreach ($items as $item) {
            $original = $createHandler->handle(
                kodeBarang: $item['original']['code'],
                namaBarang: $item['original']['name'],
                merek: $item['original']['brand'],
                ukuran: $item['original']['size'],
                hargaJual: $item['original']['price'],
                reorderPointQty: null,
                criticalThresholdQty: null,
            );

            if ($original->isFailure()) {
                Log::warning('ProductScenarioRecreatedSeeder create original gagal.', [
                    'message' => $original->message(),
                    'item' => $item,
                ]);
                continue;
            }

            $productId = $original->data()['id'] ?? null;

            if (! is_string($productId) || trim($productId) === '') {
                Log::warning('ProductScenarioRecreatedSeeder tidak mendapat product id original.', [
                    'item' => $item,
                    'data' => $original->data(),
                ]);
                continue;
            }

            $deleted = $deleteHandler->handle($productId, 'system-seeder');

            if ($deleted->isFailure()) {
                Log::warning('ProductScenarioRecreatedSeeder delete original gagal.', [
                    'message' => $deleted->message(),
                    'product_id' => $productId,
                    'item' => $item,
                ]);
                continue;
            }

            $replacement = $createHandler->handle(
                kodeBarang: $item['replacement']['code'],
                namaBarang: $item['replacement']['name'],
                merek: $item['replacement']['brand'],
                ukuran: $item['replacement']['size'],
                hargaJual: $item['replacement']['price'],
                reorderPointQty: null,
                criticalThresholdQty: null,
            );

            if ($replacement->isFailure()) {
                Log::warning('ProductScenarioRecreatedSeeder create replacement gagal.', [
                    'message' => $replacement->message(),
                    'original_product_id' => $productId,
                    'item' => $item,
                ]);
            }
        }
    }
}
