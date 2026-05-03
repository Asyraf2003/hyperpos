<?php

declare(strict_types=1);

namespace App\Application\Reporting\UseCases;

use App\Ports\Out\Reporting\DashboardInventoryOverviewReaderPort;
use App\Ports\Out\Reporting\DashboardReportCachePort;
use App\Ports\Out\Reporting\DashboardTopSellingProductReaderPort;

final class AdminDashboardSharedReportFragments
{
    public function __construct(
        private readonly DashboardInventoryOverviewReaderPort $inventory,
        private readonly DashboardTopSellingProductReaderPort $topSellingProducts,
        private readonly DashboardReportCachePort $cache,
    ) {
    }

    /**
     * @param array{today:string,active_month?:string,from:string,to:string} $period
     * @return array<string,mixed>
     */
    public function inventorySummary(array $period): array
    {
        return $this->remember(
            $this->cacheKey('inventory_summary', $period),
            fn (): array => $this->inventory->getInventorySummary(
                $period['from'],
                $period['to'],
            ),
        );
    }

    /**
     * @param array{today:string,active_month?:string,from:string,to:string} $period
     * @return list<array<string,mixed>>
     */
    public function topSellingRows(array $period, int $limit): array
    {
        return $this->remember(
            $this->cacheKey('top_selling_rows_' . $limit, $period),
            fn (): array => $this->topSellingProducts->getTopSellingProducts(
                $period['from'],
                $period['to'],
                $limit,
            ),
        );
    }

    /**
     * @template TValue of array
     * @param callable():TValue $resolver
     * @return TValue
     */
    private function remember(string $cacheKey, callable $resolver): array
    {
        return $this->cache->remember($cacheKey, $resolver);
    }

    /**
     * @param array{today:string,active_month?:string,from:string,to:string} $period
     */
    private function cacheKey(string $fragment, array $period): string
    {
        return sprintf(
            'reporting:admin_dashboard_fragment:%s:%s:%s:%s:%s',
            $fragment,
            $period['active_month'] ?? substr($period['from'], 0, 7),
            $period['today'],
            $period['from'],
            $period['to'],
        );
    }
}
