<?php

declare(strict_types=1);

namespace App\Core\Procurement\SupplierInvoice;

use App\Core\Shared\ValueObjects\Money;

final class SupplierInvoiceLine
{
    use SupplierInvoiceLineState;
    use SupplierInvoiceLineValidation;

    public static function create(
        string $id,
        string $pId,
        ?string $productKodeBarangSnapshot,
        string $productNamaBarangSnapshot,
        string $productMerekSnapshot,
        ?int $productUkuranSnapshot,
        int $qty,
        Money $total
    ): self {
        self::assertValid($id, $pId, $productNamaBarangSnapshot, $productMerekSnapshot, $qty, $total);
        $unitCost = Money::fromInt(intdiv($total->amount(), $qty));

        return new self(
            trim($id),
            trim($pId),
            self::normalizeNullableString($productKodeBarangSnapshot),
            trim($productNamaBarangSnapshot),
            trim($productMerekSnapshot),
            $productUkuranSnapshot,
            $qty,
            $total,
            $unitCost
        );
    }

    public static function rehydrate(
        string $id,
        string $pId,
        ?string $productKodeBarangSnapshot,
        string $productNamaBarangSnapshot,
        string $productMerekSnapshot,
        ?int $productUkuranSnapshot,
        int $qty,
        Money $total
    ): self {
        return self::create(
            $id,
            $pId,
            $productKodeBarangSnapshot,
            $productNamaBarangSnapshot,
            $productMerekSnapshot,
            $productUkuranSnapshot,
            $qty,
            $total
        );
    }
}
