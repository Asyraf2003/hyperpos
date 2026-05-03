<?php

declare(strict_types=1);

namespace App\Adapters\Out\Reporting;

use App\Ports\Out\Reporting\DashboardReportCachePort;
use Illuminate\Support\Facades\Cache;

final class LaravelDashboardReportCacheAdapter implements DashboardReportCachePort
{
    /**
     * @template TValue of array
     * @param callable():TValue $resolver
     * @return TValue
     */
    public function remember(string $cacheKey, callable $resolver): array
    {
        $ttlSeconds = max(
            0,
            (int) config('performance.admin_dashboard_overview_cache_ttl_seconds', 30)
        );

        if ($ttlSeconds === 0) {
            return $resolver();
        }

        return Cache::remember(
            $cacheKey,
            now()->addSeconds($ttlSeconds),
            $resolver,
        );
    }
}
