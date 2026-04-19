<?php

declare(strict_types=1);

namespace App\Core\ProductCatalog\Product;

use App\Core\Shared\ValueObjects\Money;

trait ProductFactory
{
    public static function create(
        string $id,
        ?string $kode,
        string $nama,
        string $merek,
        ?int $ukuran,
        Money $harga,
        ?int $reorderPointQty,
        ?int $criticalThresholdQty,
    ): self {
        self::assertValid(
            $id,
            $nama,
            $merek,
            $harga,
            $reorderPointQty,
            $criticalThresholdQty,
        );

        return new self(
            $id,
            self::normalizeKode($kode),
            trim($nama),
            trim($merek),
            $ukuran,
            $harga,
            $reorderPointQty,
            $criticalThresholdQty,
        );
    }

    public static function rehydrate(
        string $id,
        ?string $kode,
        string $nama,
        string $merek,
        ?int $ukuran,
        Money $harga,
        ?int $reorderPointQty,
        ?int $criticalThresholdQty,
    ): self {
        self::assertValid(
            $id,
            $nama,
            $merek,
            $harga,
            $reorderPointQty,
            $criticalThresholdQty,
        );

        return new self(
            $id,
            self::normalizeKode($kode),
            trim($nama),
            trim($merek),
            $ukuran,
            $harga,
            $reorderPointQty,
            $criticalThresholdQty,
        );
    }
}
