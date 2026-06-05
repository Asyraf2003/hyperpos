<?php

declare(strict_types=1);

namespace App\Application\ProductCatalog\DTO;

final readonly class ProductLookupRow
{
    public function __construct(
        public string $id,
        public ?string $kodeBarang,
        public string $namaBarang,
        public string $merek,
        public ?int $ukuran,
        public int $availableStock,
        public int $defaultUnitPriceRupiah,
        public int $minimumUnitPriceRupiah,
    ) {
    }

    public function label(): string
    {
        $parts = [
            $this->namaBarang,
            $this->merek,
        ];

        if ($this->ukuran !== null) {
            $parts[] = (string) $this->ukuran;
        }

        $label = implode(' — ', $parts);

        if ($this->kodeBarang !== null) {
            $label .= ' (' . $this->kodeBarang . ')';
        }

        return $label;
    }
}
