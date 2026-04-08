<?php

declare(strict_types=1);

namespace Database\Seeders\Product;

use App\Application\ProductCatalog\UseCases\CreateProductHandler;
use App\Application\ProductCatalog\UseCases\UpdateProductHandler;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;

final class ProductScenarioEditedSeeder extends Seeder
{
    public function run(
        CreateProductHandler $createHandler,
        UpdateProductHandler $updateHandler,
    ): void {
        $items = ProductSeedCatalog::all()['active_edited'];

        foreach ($items as $item) {
            $created = $createHandler->handle(
                kodeBarang: $item['create']['code'],
                namaBarang: $item['create']['name'],
                merek: $item['create']['brand'],
                ukuran: $item['create']['size'],
                hargaJual: $item['create']['price'],
            );

            if ($created->isFailure()) {
                Log::warning('ProductScenarioEditedSeeder create gagal.', [
                    'message' => $created->message(),
                    'item' => $item,
                ]);
                continue;
            }

            $productId = $created->data()['id'] ?? null;

            if (! is_string($productId) || trim($productId) === '') {
                Log::warning('ProductScenarioEditedSeeder tidak mendapat product id setelah create.', [
                    'item' => $item,
                    'data' => $created->data(),
                ]);
                continue;
            }

            $updated = $updateHandler->handle(
                productId: $productId,
                kodeBarang: $item['update']['code'],
                namaBarang: $item['update']['name'],
                merek: $item['update']['brand'],
                ukuran: $item['update']['size'],
                hargaJual: $item['update']['price'],
            );

            if ($updated->isFailure()) {
                Log::warning('ProductScenarioEditedSeeder update gagal.', [
                    'message' => $updated->message(),
                    'product_id' => $productId,
                    'item' => $item,
                ]);
            }
        }
    }
}
