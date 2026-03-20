<?php

declare(strict_types=1);

namespace App\Core\Procurement\SupplierInvoice;

use App\Core\Shared\ValueObjects\Money;
use DateTimeImmutable;

trait SupplierInvoiceState
{
    /** @param list<SupplierInvoiceLine> $lines */
    private function __construct(
        private string $id,
        private string $supplierId,
        private string $supplierNamaPtPengirimSnapshot,
        private DateTimeImmutable $tanggalPengiriman,
        private DateTimeImmutable $jatuhTempo,
        private array $lines,
        private Money $grandTotalRupiah,
    ) {
    }

    public function id(): string { return $this->id; }
    public function supplierId(): string { return $this->supplierId; }
    public function supplierNamaPtPengirimSnapshot(): string { return $this->supplierNamaPtPengirimSnapshot; }
    public function tanggalPengiriman(): DateTimeImmutable { return $this->tanggalPengiriman; }
    public function jatuhTempo(): DateTimeImmutable { return $this->jatuhTempo; }
    /** @return list<SupplierInvoiceLine> */
    public function lines(): array { return $this->lines; }
    public function grandTotalRupiah(): Money { return $this->grandTotalRupiah; }
}
