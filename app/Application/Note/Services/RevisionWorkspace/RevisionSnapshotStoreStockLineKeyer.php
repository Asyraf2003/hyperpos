<?php

declare(strict_types=1);

namespace App\Application\Note\Services\RevisionWorkspace;

final class RevisionSnapshotStoreStockLineKeyer
{
    public function fromParts(string $productId, int $qty, int $lineTotalRupiah): string
    {
        return trim($productId) . '|' . $qty . '|' . $lineTotalRupiah;
    }
}
