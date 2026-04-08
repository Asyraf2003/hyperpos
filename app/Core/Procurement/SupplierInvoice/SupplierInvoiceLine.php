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
        int $lineNo,
        string $productId,
        ?string $productKodeBarangSnapshot,
        string $productNamaBarangSnapshot,
        string $productMerekSnapshot,
        ?int $productUkuranSnapshot,
        int $qtyPcs,
        Money $lineTotalRupiah
    ): self {
        self::assertValid(
            $id,
            $lineNo,
            $productId,
            $productNamaBarangSnapshot,
            $productMerekSnapshot,
            $qtyPcs,
            $lineTotalRupiah
        );

        $unitCostRupiah = Money::fromInt(intdiv($lineTotalRupiah->amount(), $qtyPcs));

        return new self(
            trim($id),
            $lineNo,
            trim($productId),
            self::normalizeNullableString($productKodeBarangSnapshot),
            trim($productNamaBarangSnapshot),
            trim($productMerekSnapshot),
            $productUkuranSnapshot,
            $qtyPcs,
            $lineTotalRupiah,
            $unitCostRupiah
        );
    }

    public static function rehydrate(
        string $id,
        int $lineNo,
        string $productId,
        ?string $productKodeBarangSnapshot,
        string $productNamaBarangSnapshot,
        string $productMerekSnapshot,
        ?int $productUkuranSnapshot,
        int $qtyPcs,
        Money $lineTotalRupiah
    ): self {
        return self::create(
            $id,
            $lineNo,
            $productId,
            $productKodeBarangSnapshot,
            $productNamaBarangSnapshot,
            $productMerekSnapshot,
            $productUkuranSnapshot,
            $qtyPcs,
            $lineTotalRupiah
        );
    }
}
