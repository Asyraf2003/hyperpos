<?php

declare(strict_types=1);

namespace App\Core\Procurement\SupplierInvoice;

use App\Core\Shared\Exceptions\DomainException;
use App\Core\Shared\ValueObjects\Money;

trait SupplierInvoiceLineValidation
{
    private static function assertValid(
        string $id,
        string $pId,
        string $productNamaBarangSnapshot,
        string $productMerekSnapshot,
        int $qty,
        Money $total
    ): void {
        if (trim($id) === '') throw new DomainException('ID wajib ada.');
        if (trim($pId) === '') throw new DomainException('Product ID wajib ada.');
        if (trim($productNamaBarangSnapshot) === '') throw new DomainException('Snapshot nama barang wajib ada.');
        if (trim($productMerekSnapshot) === '') throw new DomainException('Snapshot merek wajib ada.');
        if ($qty <= 0) throw new DomainException('Qty harus > 0.');
        if (!$total->greaterThan(Money::zero())) throw new DomainException('Total harus > 0.');
        if ($total->amount() % $qty !== 0) throw new DomainException('Total harus habis dibagi qty.');
    }

    private static function normalizeNullableString(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $normalized = trim($value);

        return $normalized === '' ? null : $normalized;
    }
}
