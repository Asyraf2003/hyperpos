<?php

declare(strict_types=1);

namespace App\Application\Reporting\UseCases;

final class AdminDashboardAnalyticsPayloadBuilder
{
    public function __construct(
        private readonly AdminDashboardSharedReportFragments $sharedFragments,
        private readonly GetDashboardOperationalPerformanceDatasetHandler $operationalPerformance,
        private readonly AdminDashboardAnalyticsChartsPayloadBuilder $charts,
    ) {
    }

    /**
     * @param array{
     *   today:string,
     *   active_month:string,
     *   from:string,
     *   to:string
     * } $period
     */
    public function build(array $period): array
    {
        $inventorySummary = $this->sharedFragments->inventorySummary($period);

        $topSellingRows = $this->sharedFragments->topSellingRows($period, 5);

        $operationalPerformanceDataset = $this->operationalPerformance->handle(
            $period['from'],
            $period['to'],
        );

        return [
            'period' => [
                'window_type' => 'month_to_date',
                'anchor_date' => $period['today'],
                'active_month' => $period['active_month'],
                'date_from' => $period['from'],
                'date_to' => $period['to'],
                'granularity' => 'daily',
                'generated_at' => now()->toISOString(),
            ],
            'cash_change_denominations' => $operationalPerformanceDataset['cash_change_denominations'] ?? [],
            'charts' => $this->charts->build(
                $inventorySummary,
                $topSellingRows,
                $operationalPerformanceDataset,
                $period,
            ),
        ];
    }
}
