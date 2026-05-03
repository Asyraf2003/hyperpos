<?php

declare(strict_types=1);

namespace App\Application\Reporting\UseCases;

use App\Ports\Out\Reporting\DashboardReportCachePort;

final class GetAdminDashboardAnalyticsHandler
{
    public function __construct(
        private readonly AdminDashboardAnalyticsPayloadBuilder $builder,
        private readonly DashboardReportCachePort $cache,
    ) {
    }

    public function handle(?string $month = null): array
    {
        $period = AdminDashboardAnalyticsPeriod::build($month);
        $cacheKey = sprintf(
            'reporting:admin_dashboard_analytics:%s:%s:%s:%s',
            $period['active_month'],
            $period['today'],
            $period['from'],
            $period['to'],
        );

        return $this->cache->remember(
            $cacheKey,
            fn (): array => $this->builder->build($period),
        );
    }
}
