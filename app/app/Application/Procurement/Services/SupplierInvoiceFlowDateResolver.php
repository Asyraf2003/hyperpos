<?php

declare(strict_types=1);

namespace App\Application\Procurement\Services;

use App\Core\Shared\Exceptions\DomainException;
use DateTimeImmutable;

final class SupplierInvoiceFlowDateResolver
{
    /**
     * @return array{0:DateTimeImmutable,1:?DateTimeImmutable}
     */
    public function resolve(string $tglKirim, bool $autoRec, ?string $tglTerima): array
    {
        $dateKirim = DateTimeImmutable::createFromFormat('!Y-m-d', trim($tglKirim))
            ?: throw new DomainException('Tgl kirim tidak valid.');

        $dateTerima = $autoRec
            ? (
                DateTimeImmutable::createFromFormat('!Y-m-d', trim($tglTerima ?? $tglKirim))
                ?: throw new DomainException('Tgl terima tidak valid.')
            )
            : null;

        return [$dateKirim, $dateTerima];
    }
}
