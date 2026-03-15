<?php

declare(strict_types=1);

namespace App\Core\Procurement\SupplierInvoice;

use App\Core\Shared\ValueObjects\Money;
use DateTimeImmutable;

final class SupplierInvoice
{
    use SupplierInvoiceState;
    use SupplierInvoiceValidation;

    /** @param list<SupplierInvoiceLine> $lines */
    public static function create(string $id, string $sId, DateTimeImmutable $tgl, array $lines): self
    {
        self::assertValid($id, $sId, $lines);
        return new self($id, trim($sId), $tgl, self::calculateJatuhTempo($tgl), array_values($lines), self::calculateGrandTotalRupiah($lines));
    }

    /** @param list<SupplierInvoiceLine> $lines */
    public static function rehydrate(string $id, string $sId, DateTimeImmutable $tgl, array $lines): self
    {
        self::assertValid($id, $sId, $lines);
        return new self($id, trim($sId), $tgl, self::calculateJatuhTempo($tgl), array_values($lines), self::calculateGrandTotalRupiah($lines));
    }
}
