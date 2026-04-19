<?php

declare(strict_types=1);

namespace App\Core\Procurement\SupplierInvoice;

use App\Core\Shared\Exceptions\DomainException;
use App\Core\Shared\ValueObjects\Money;

trait SupplierInvoiceLineValidation
{
    private static function assertValid(
        string $id,
        int $lineNo,
        string $productId,
        string $productNamaBarangSnapshot,
        string $productMerekSnapshot,
        int $qtyPcs,
        Money $lineTotalRupiah
    ): void {
        if (trim($id) === '') throw new DomainException('ID line wajib ada.');
        if ($lineNo < 1) throw new DomainException('Nomor baris wajib >= 1.');
        if (trim($productId) === '') throw new DomainException('Product ID wajib ada.');
        if (trim($productNamaBarangSnapshot) === '') throw new DomainException('Snapshot nama barang wajib ada.');
        if (trim($productMerekSnapshot) === '') throw new DomainException('Snapshot merek wajib ada.');
        if ($qtyPcs < 1) throw new DomainException('Qty wajib lebih dari 0.');
        if ($lineTotalRupiah->amount() < 1) throw new DomainException('Total line wajib lebih dari 0.');
        if ($lineTotalRupiah->amount() % $qtyPcs !== 0) {
            throw new DomainException('Total line harus habis dibagi qty.');
        }
    }
}
