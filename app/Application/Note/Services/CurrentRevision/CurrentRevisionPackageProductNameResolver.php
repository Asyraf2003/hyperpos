<?php

declare(strict_types=1);

namespace App\Application\Note\Services\CurrentRevision;

final class CurrentRevisionPackageProductNameResolver
{
    /**
     * @param array<string, mixed> $line
     * @param array<string, string> $currentNames
     */
    public static function displayName(array $line, string $productId, array $currentNames): string
    {
        foreach (['product_name_snapshot', 'product_nama_barang_snapshot'] as $snapshotKey) {
            $snapshotName = trim((string) ($line[$snapshotKey] ?? ''));

            if ($snapshotName !== '') {
                return $snapshotName;
            }
        }

        return $currentNames[$productId] ?? $productId;
    }
}
