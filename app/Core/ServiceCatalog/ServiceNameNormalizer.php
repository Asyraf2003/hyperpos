<?php

declare(strict_types=1);

namespace App\Core\ServiceCatalog;

final class ServiceNameNormalizer
{
    public function normalize(string $value): string
    {
        $lower = mb_strtolower(trim($value), 'UTF-8');
        $normalized = preg_replace('/[^\p{L}\p{N}]+/u', ' ', $lower) ?? $lower;
        $normalized = preg_replace('/\s+/', ' ', trim($normalized)) ?? trim($normalized);

        return $normalized;
    }
}
