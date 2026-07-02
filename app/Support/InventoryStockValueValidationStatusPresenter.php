<?php

declare(strict_types=1);

namespace App\Support;

final class InventoryStockValueValidationStatusPresenter
{
    /**
     * @param array<string, mixed> $summary
     */
    public static function cardClass(array $summary, string $key): string
    {
        return self::isHealthy($summary, $key) ? 'border-success' : 'border-danger';
    }

    /**
     * @param array<string, mixed> $summary
     */
    public static function textClass(array $summary, string $key): string
    {
        return self::isHealthy($summary, $key) ? 'text-success' : 'text-danger';
    }

    /**
     * @param array<string, mixed> $summary
     */
    public static function badgeClass(array $summary, string $key): string
    {
        return self::isHealthy($summary, $key)
            ? 'bg-light-success text-success'
            : 'bg-light-danger text-danger';
    }

    /**
     * @param array<string, mixed> $summary
     */
    public static function badgeText(array $summary, string $key): string
    {
        return self::isHealthy($summary, $key) ? 'Sehat' : 'Perlu Dicek';
    }

    /**
     * @param array<string, mixed> $summary
     */
    private static function isHealthy(array $summary, string $key): bool
    {
        return (int) ($summary[$key] ?? 0) === 0;
    }
}
