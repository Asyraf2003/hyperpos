<?php

declare(strict_types=1);

namespace App\Core\Procurement\SupplierInvoice;

use DateTimeImmutable;

final class SupplierInvoice
{
    use SupplierInvoiceState;
    use SupplierInvoiceValidation;

    /** @param list<SupplierInvoiceLine> $lines */
    public static function create(
        string $id,
        string $sId,
        string $supplierNamaPtPengirimSnapshot,
        DateTimeImmutable $tgl,
        array $lines
    ): self {
        self::assertValid($id, $sId, $supplierNamaPtPengirimSnapshot, $lines);

        return new self(
            trim($id),
            trim($sId),
            trim($supplierNamaPtPengirimSnapshot),
            $tgl,
            self::calculateJatuhTempo($tgl),
            array_values($lines),
            self::calculateGrandTotalRupiah($lines)
        );
    }

    /** @param list<SupplierInvoiceLine> $lines */
    public static function rehydrate(
        string $id,
        string $sId,
        string $supplierNamaPtPengirimSnapshot,
        DateTimeImmutable $tgl,
        array $lines
    ): self {
        self::assertValid($id, $sId, $supplierNamaPtPengirimSnapshot, $lines);

        return new self(
            trim($id),
            trim($sId),
            trim($supplierNamaPtPengirimSnapshot),
            $tgl,
            self::calculateJatuhTempo($tgl),
            array_values($lines),
            self::calculateGrandTotalRupiah($lines)
        );
    }
}
