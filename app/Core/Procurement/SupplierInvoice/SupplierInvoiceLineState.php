<?php

declare(strict_types=1);

namespace App\Core\Procurement\SupplierInvoice;

use App\Core\Shared\ValueObjects\Money;

trait SupplierInvoiceLineState
{
    private function __construct(
        private string $id,
        private string $productId,
        private ?string $productKodeBarangSnapshot,
        private string $productNamaBarangSnapshot,
        private string $productMerekSnapshot,
        private ?int $productUkuranSnapshot,
        private int $qtyPcs,
        private Money $lineTotalRupiah,
        private Money $unitCostRupiah,
    ) {
    }

    public function id(): string { return $this->id; }
    public function productId(): string { return $this->productId; }
    public function productKodeBarangSnapshot(): ?string { return $this->productKodeBarangSnapshot; }
    public function productNamaBarangSnapshot(): string { return $this->productNamaBarangSnapshot; }
    public function productMerekSnapshot(): string { return $this->productMerekSnapshot; }
    public function productUkuranSnapshot(): ?int { return $this->productUkuranSnapshot; }
    public function qtyPcs(): int { return $this->qtyPcs; }
    public function lineTotalRupiah(): Money { return $this->lineTotalRupiah; }
    public function unitCostRupiah(): Money { return $this->unitCostRupiah; }
}
