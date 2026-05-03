<?php

declare(strict_types=1);

namespace App\Application\Reporting\UseCases;

use App\Ports\Out\Reporting\DashboardReportCachePort;

final class GetAdminDashboardOverviewHandler
{
    public function __construct(
        private readonly AdminDashboardOverviewPayloadBuilder $builder,
        private readonly DashboardReportCachePort $cache,
    ) {
    }

    public function handle(?string $month = null): array
    {
        $period = AdminDashboardOverviewPeriod::build($month);
        $cacheKey = sprintf(
            'reporting:admin_dashboard_overview:%s:%s:%s:%s',
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
