<?php

declare(strict_types=1);

namespace Database\Seeders\Product;

use App\Application\ProductCatalog\UseCases\CreateProductHandler;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;

final class ProductScenarioActiveBasicSeeder extends Seeder
{
    public function run(CreateProductHandler $handler): void
    {
        $items = ProductSeedCatalog::all()['active_basic'];

        foreach ($items as $item) {
            $result = $handler->handle(
                kodeBarang: $item['code'],
                namaBarang: $item['name'],
                merek: $item['brand'],
                ukuran: $item['size'],
                hargaJual: $item['price'],
                reorderPointQty: null,
                criticalThresholdQty: null,
            );

            if ($result->isFailure()) {
                Log::warning('ProductScenarioActiveBasicSeeder gagal.', [
                    'message' => $result->message(),
                    'item' => $item,
                ]);
            }
        }
    }
}
