<?php

declare(strict_types=1);

namespace App\Application\ProductCatalog\UseCases;

use App\Application\Shared\DTO\Result;
use App\Core\ProductCatalog\Product\Product;
use App\Core\Shared\Exceptions\DomainException;
use App\Core\Shared\ValueObjects\Money;
use App\Ports\Out\ProductCatalog\ProductDuplicateCheckerPort;
use App\Ports\Out\ProductCatalog\ProductWriterPort;
use App\Ports\Out\UuidPort;

final class CreateProductHandler
{
    public function __construct(
        private readonly ProductWriterPort $products,
        private readonly ProductDuplicateCheckerPort $duplicates,
        private readonly UuidPort $uuid,
    ) {
    }

    public function handle(
        ?string $kodeBarang,
        string $namaBarang,
        string $merek,
        ?int $ukuran,
        int $hargaJual,
    ): Result {
        $normalizedKodeBarang = $this->normalizeKodeBarang($kodeBarang);
        $normalizedNamaBarang = trim($namaBarang);
        $normalizedMerek = trim($merek);

        if ($this->duplicates->hasConflictForCreate(
            $normalizedKodeBarang,
            $normalizedNamaBarang,
            $normalizedMerek,
            $ukuran,
        )) {
            return Result::failure(
                'Product dengan kombinasi data ini sudah ada.',
                ['product' => ['PRODUCT_DUPLICATE']]
            );
        }

        try {
            $product = Product::create(
                $this->uuid->generate(),
                $normalizedKodeBarang,
                $normalizedNamaBarang,
                $normalizedMerek,
                $ukuran,
                Money::fromInt($hargaJual),
            );
        } catch (DomainException $e) {
            return Result::failure(
                $e->getMessage(),
                ['product' => ['INVALID_PRODUCT']]
            );
        }

        $this->products->create($product);

        return Result::success(
            [
                'id' => $product->id(),
                'kode_barang' => $product->kodeBarang(),
                'nama_barang' => $product->namaBarang(),
                'merek' => $product->merek(),
                'ukuran' => $product->ukuran(),
                'harga_jual' => $product->hargaJual()->amount(),
            ],
            'Product master berhasil dibuat.'
        );
    }

    private function normalizeKodeBarang(?string $kodeBarang): ?string
    {
        if ($kodeBarang === null) {
            return null;
        }

        $normalized = trim($kodeBarang);

        return $normalized === '' ? null : $normalized;
    }
}
