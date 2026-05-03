<?php

declare(strict_types=1);

namespace App\Ports\Out\Reporting;

interface DashboardReportCachePort
{
    /**
     * @template TValue of array
     * @param callable():TValue $resolver
     * @return TValue
     */
    public function remember(string $cacheKey, callable $resolver): array;
}
