<?php

declare(strict_types=1);

namespace App\Application\ProductCatalog\UseCases\Concerns;

trait NormalizesProductMasterInput
{
    private function normalizeKodeBarang(?string $kodeBarang): ?string
    {
        if ($kodeBarang === null) {
            return null;
        }

        $normalized = trim($kodeBarang);

        return $normalized === '' ? null : $normalized;
    }
}
