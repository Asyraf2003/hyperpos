<?php

declare(strict_types=1);

namespace Database\Seeders\Product;

use App\Application\ProductCatalog\UseCases\CreateProductHandler;
use App\Application\ProductCatalog\UseCases\UpdateProductHandler;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;

final class ProductVolumeSeeder extends Seeder
{
    public function run(
        CreateProductHandler $createHandler,
        UpdateProductHandler $updateHandler,
    ): void {
        $this->seedActiveClean($createHandler, 270);
        $this->seedActiveEdited($createHandler, $updateHandler, 30);
    }

    private function seedActiveClean(CreateProductHandler $handler, int $count): void
    {
        for ($i = 1; $i <= $count; $i++) {
            $created = $handler->handle(...$this->buildCleanPayload($i));

            if ($created->isFailure()) {
                Log::warning('ProductVolumeSeeder active clean gagal.', [
                    'message' => $created->message(),
                    'index' => $i,
                ]);
            }
        }
    }

    private function seedActiveEdited(
        CreateProductHandler $createHandler,
        UpdateProductHandler $updateHandler,
        int $count,
    ): void {
        for ($i = 1; $i <= $count; $i++) {
            $created = $createHandler->handle(...$this->buildEditedCreatePayload($i));

            if ($created->isFailure()) {
                Log::warning('ProductVolumeSeeder active edited create gagal.', [
                    'message' => $created->message(),
                    'index' => $i,
                ]);
                continue;
            }

            $productId = $created->data()['id'] ?? null;

            if (! is_string($productId) || trim($productId) === '') {
                Log::warning('ProductVolumeSeeder active edited tidak mendapat product id.', [
                    'index' => $i,
                    'data' => $created->data(),
                ]);
                continue;
            }

            $updated = $updateHandler->handle(
                productId: $productId,
                ...$this->buildEditedUpdatePayload($i),
            );

            if ($updated->isFailure()) {
                Log::warning('ProductVolumeSeeder active edited update gagal.', [
                    'message' => $updated->message(),
                    'product_id' => $productId,
                    'index' => $i,
                ]);
            }
        }
    }

    private function buildCleanPayload(int $index): array
    {
        return [
            'kodeBarang' => sprintf('PRD-V2-ACT-%03d', $index),
            'namaBarang' => $this->buildName($index, false),
            'merek' => $this->brands()[$index % count($this->brands())],
            'ukuran' => $this->buildSize($index),
            'hargaJual' => 28000 + (($index * 13750) % 420000),
        ];
    }

    private function buildEditedCreatePayload(int $index): array
    {
        return [
            'kodeBarang' => sprintf('PRD-V2-EDT-%03d', $index),
            'namaBarang' => $this->buildName($index + 270, true) . ' Draft',
            'merek' => $this->brands()[($index + 2) % count($this->brands())],
            'ukuran' => $this->buildSize($index + 270),
            'hargaJual' => 35000 + (($index * 11000) % 360000),
        ];
    }

    private function buildEditedUpdatePayload(int $index): array
    {
        return [
            'kodeBarang' => sprintf('PRD-V2-EDT-%03d', $index),
            'namaBarang' => $this->buildName($index + 270, true),
            'merek' => $this->brands()[($index + 5) % count($this->brands())],
            'ukuran' => $this->buildSize($index + 271),
            'hargaJual' => 42000 + (($index * 12500) % 390000),
        ];
    }

    private function buildName(int $index, bool $edited): string
    {
        $parts = ['Filter Oli', 'Kampas Rem', 'Busi', 'V Belt', 'Roller', 'Rantai', 'Piston Kit', 'Shockbreaker', 'Ban Luar', 'Ban Dalam', 'Seal Shock', 'Kabel Gas', 'Spion', 'CDI', 'Kanvas Kopling'];
        $vehicles = ['Beat', 'Vario', 'NMAX', 'PCX', 'Mio', 'Aerox', 'Jupiter Z', 'Supra X', 'Scoopy', 'Satria FU', 'Megapro', 'Tiger'];
        $variants = ['Std', 'Plus', 'Pro', 'Street', 'Touring', 'Max', 'Prime', 'Racing', 'X', 'Sport'];

        $part = $parts[$index % count($parts)];
        $vehicle = $vehicles[$index % count($vehicles)];
        $variant = $variants[$index % count($variants)];

        return $edited
            ? sprintf('%s %s %s Rev', $part, $vehicle, $variant)
            : sprintf('%s %s %s', $part, $vehicle, $variant);
    }

    private function buildSize(int $index): ?int
    {
        $sizes = [null, 80, 90, 100, 110, 125, 150, 200, 250, 300, 428];

        return $sizes[$index % count($sizes)];
    }

    private function brands(): array
    {
        return ['Federal', 'Astra', 'Nissin', 'NGK', 'Stanley', 'Bando', 'Yamaha', 'YGP', 'DID', 'Mikuni', 'FIM', 'KYB', 'FDR', 'Showa', 'BRT', 'FCC'];
    }
}
