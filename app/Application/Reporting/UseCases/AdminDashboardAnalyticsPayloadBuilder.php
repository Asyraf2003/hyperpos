<?php

declare(strict_types=1);

namespace App\Application\Reporting\UseCases;

use App\Ports\Out\ClockPort;
use DateTimeZone;

final class AdminDashboardAnalyticsPayloadBuilder
{
    public function __construct(
        private readonly AdminDashboardSharedReportFragments $sharedFragments,
        private readonly GetDashboardOperationalPerformanceDatasetHandler $operationalPerformance,
        private readonly AdminDashboardAnalyticsChartsPayloadBuilder $charts,
        private readonly ClockPort $clock,
    ) {
    }

    /**
     * @param array{
     *   today:string,
     *   active_month:string,
     *   from:string,
     *   to:string
     * } $period
     *
     * @return array<string, mixed>
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
                'generated_at' => $this->clock->now()
                    ->setTimezone(new DateTimeZone('UTC'))
                    ->format('Y-m-d\TH:i:s.u\Z'),
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
