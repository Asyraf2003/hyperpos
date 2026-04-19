<?php

declare(strict_types=1);

namespace App\Core\ProductCatalog\Product;

use App\Core\Shared\Exceptions\DomainException;
use App\Core\Shared\ValueObjects\Money;

trait ProductValidation
{
    private static function assertValid(
        string $id,
        string $nama,
        string $merek,
        Money $harga,
        ?int $reorderPointQty,
        ?int $criticalThresholdQty,
    ): void {
        if (trim($id) === '') throw new DomainException('Product id wajib ada.');
        if (trim($nama) === '') throw new DomainException('Nama barang wajib ada.');
        if (trim($merek) === '') throw new DomainException('Merek wajib ada.');
        if (! $harga->greaterThan(Money::zero())) throw new DomainException('Harga jual harus > 0.');

        if (($reorderPointQty === null) xor ($criticalThresholdQty === null)) {
            throw new DomainException('Reorder point dan batas kritis harus diisi berpasangan atau dikosongkan keduanya.');
        }

        if ($reorderPointQty !== null && $reorderPointQty < 0) {
            throw new DomainException('Reorder point tidak boleh negatif.');
        }

        if ($criticalThresholdQty !== null && $criticalThresholdQty < 0) {
            throw new DomainException('Batas kritis tidak boleh negatif.');
        }

        if (
            $reorderPointQty !== null &&
            $criticalThresholdQty !== null &&
            $criticalThresholdQty > $reorderPointQty
        ) {
            throw new DomainException('Batas kritis tidak boleh lebih besar dari reorder point.');
        }
    }

    private static function normalizeKode(?string $kode): ?string
    {
        if ($kode === null) return null;

        $val = trim($kode);

        return $val === '' ? null : $val;
    }
}
