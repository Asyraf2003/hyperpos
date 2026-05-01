<?php

declare(strict_types=1);

namespace App\Application\Reporting\UseCases;

use Illuminate\Support\Facades\Cache;

final class GetAdminDashboardAnalyticsHandler
{
    public function __construct(
        private readonly AdminDashboardAnalyticsPayloadBuilder $builder,
    ) {
    }

    public function handle(?string $month = null): array
    {
        $period = AdminDashboardAnalyticsPeriod::build($month);
        $ttlSeconds = max(
            0,
            (int) config('performance.admin_dashboard_overview_cache_ttl_seconds', 30)
        );

        if ($ttlSeconds === 0) {
            return $this->builder->build($period);
        }

        $cacheKey = sprintf(
            'reporting:admin_dashboard_analytics:%s:%s:%s:%s',
            $period['active_month'],
            $period['today'],
            $period['from'],
            $period['to'],
        );

        return Cache::remember(
            $cacheKey,
            now()->addSeconds($ttlSeconds),
            fn (): array => $this->builder->build($period),
        );
    }
}
