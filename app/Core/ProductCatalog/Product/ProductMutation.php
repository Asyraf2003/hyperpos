<?php

declare(strict_types=1);

namespace App\Core\ProductCatalog\Product;

use App\Core\Shared\ValueObjects\Money;

trait ProductMutation
{
    public function updateMaster(
        ?string $kode,
        string $nama,
        string $merek,
        ?int $ukuran,
        Money $harga,
        ?int $reorderPointQty,
        ?int $criticalThresholdQty,
    ): void {
        self::assertValid(
            $this->id,
            $nama,
            $merek,
            $harga,
            $reorderPointQty,
            $criticalThresholdQty,
        );

        $this->kodeBarang = self::normalizeKode($kode);
        $this->namaBarang = trim($nama);
        $this->merek = trim($merek);
        $this->ukuran = $ukuran;
        $this->hargaJual = $harga;
        $this->reorderPointQty = $reorderPointQty;
        $this->criticalThresholdQty = $criticalThresholdQty;
    }
}
