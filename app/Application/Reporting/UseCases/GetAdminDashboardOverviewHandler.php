<?php

declare(strict_types=1);

namespace App\Application\Reporting\UseCases;

use Illuminate\Support\Facades\Cache;

final class GetAdminDashboardOverviewHandler
{
    public function __construct(
        private readonly AdminDashboardOverviewPayloadBuilder $builder,
    ) {
    }

    public function handle(): array
    {
        $period = AdminDashboardOverviewPeriod::build();
        $ttlSeconds = max(
            0,
            (int) config('performance.admin_dashboard_overview_cache_ttl_seconds', 30)
        );

        if ($ttlSeconds === 0) {
            return $this->builder->build($period);
        }

        $cacheKey = sprintf(
            'reporting:admin_dashboard_overview:%s:%s:%s',
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
