<?php

declare(strict_types=1);

namespace Database\Seeders\Product;

use App\Application\ProductCatalog\UseCases\CreateProductHandler;
use App\Application\ProductCatalog\UseCases\SoftDeleteProductHandler;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;

final class ProductScenarioSoftDeletedSeeder extends Seeder
{
    public function run(
        CreateProductHandler $createHandler,
        SoftDeleteProductHandler $deleteHandler,
    ): void {
        $items = ProductSeedCatalog::all()['soft_deleted'];

        foreach ($items as $item) {
            $created = $createHandler->handle(
                kodeBarang: $item['create']['code'],
                namaBarang: $item['create']['name'],
                merek: $item['create']['brand'],
                ukuran: $item['create']['size'],
                hargaJual: $item['create']['price'],
            );

            if ($created->isFailure()) {
                Log::warning('ProductScenarioSoftDeletedSeeder create gagal.', [
                    'message' => $created->message(),
                    'item' => $item,
                ]);
                continue;
            }

            $productId = $created->data()['id'] ?? null;

            if (! is_string($productId) || trim($productId) === '') {
                Log::warning('ProductScenarioSoftDeletedSeeder tidak mendapat product id setelah create.', [
                    'item' => $item,
                    'data' => $created->data(),
                ]);
                continue;
            }

            $deleted = $deleteHandler->handle($productId, 'system-seeder');

            if ($deleted->isFailure()) {
                Log::warning('ProductScenarioSoftDeletedSeeder delete gagal.', [
                    'message' => $deleted->message(),
                    'product_id' => $productId,
                    'item' => $item,
                ]);
            }
        }
    }
}
