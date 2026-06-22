<?php

declare(strict_types=1);

namespace App\Application\Note\Services;

use App\Core\Shared\Exceptions\DomainException;

final class CreateTransactionWorkspaceRevisionSnapshotUnitPriceResolver
{
    /**
     * @param array<string, mixed> $line
     */
    public function resolve(array $line, int $catalogUnitPrice): int
    {
        if (($line['_server_trusted_revision_snapshot'] ?? false) !== true) {
            return $catalogUnitPrice;
        }

        $snapshotUnitPrice = $line['unit_price_rupiah'] ?? null;

        if (! is_int($snapshotUnitPrice) || $snapshotUnitPrice <= 0) {
            throw new DomainException('Harga satuan produk snapshot revisi tidak valid.');
        }

        return $snapshotUnitPrice;
    }
}
