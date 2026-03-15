<?php

declare(strict_types=1);

namespace App\Core\Procurement\SupplierInvoice;

use App\Core\Shared\Exceptions\DomainException;
use App\Core\Shared\ValueObjects\Money;
use DateTimeImmutable;

trait SupplierInvoiceValidation
{
    /** @param list<SupplierInvoiceLine> $lines */
    private static function assertValid(string $id, string $sId, array $lines): void
    {
        if (trim($id) === '') throw new DomainException('ID wajib ada.');
        if (trim($sId) === '') throw new DomainException('Supplier ID wajib ada.');
        if ($lines === []) throw new DomainException('Minimal 1 line.');
        foreach ($lines as $l) {
            if (!$l instanceof SupplierInvoiceLine) throw new DomainException('Line tidak valid.');
        }
    }

    private static function calculateJatuhTempo(DateTimeImmutable $tgl): DateTimeImmutable
    {
        $m = (int)$tgl->format('n') + 1;
        $y = (int)$tgl->format('Y');
        if ($m > 12) { $m = 1; $y++; }
        $last = (int)(new DateTimeImmutable(sprintf('%04d-%02d-01', $y, $m)))->modify('last day of this month')->format('j');
        return $tgl->setDate($y, $m, min((int)$tgl->format('j'), $last));
    }

    /** @param list<SupplierInvoiceLine> $lines */
    private static function calculateGrandTotalRupiah(array $lines): Money
    {
        $total = Money::zero();
        foreach ($lines as $l) $total = $total->add($l->lineTotalRupiah());
        return $total;
    }
}
