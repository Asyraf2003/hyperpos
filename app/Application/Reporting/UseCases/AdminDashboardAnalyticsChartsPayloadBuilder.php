<?php

declare(strict_types=1);

namespace App\Application\Reporting\UseCases;

use App\Application\Reporting\UseCases\Charts\BuildCashflowLineChart;
use App\Application\Reporting\UseCases\Charts\BuildOperationalPerformanceBarChart;
use App\Application\Reporting\UseCases\Charts\BuildStockStatusDonutChart;
use App\Application\Reporting\UseCases\Charts\BuildTopSellingBarChart;

final class AdminDashboardAnalyticsChartsPayloadBuilder
{
    public function __construct(
        private readonly BuildStockStatusDonutChart $stockStatusDonut,
        private readonly BuildTopSellingBarChart $topSellingBar,
        private readonly BuildCashflowLineChart $cashflowLine,
        private readonly BuildOperationalPerformanceBarChart $operationalPerformanceBar,
    ) {
    }

    /**
     * @param array<string,mixed> $inventorySummary
     * @param list<array<string,mixed>> $topSellingRows
     * @param list<array<string,mixed>> $cashLedgerRows
     * @param array<string,mixed> $operationalPerformanceDataset
     * @param array{from:string,to:string} $period
     *
     * @return array<string,mixed>
     */
    public function build(
        array $inventorySummary,
        array $topSellingRows,
        array $cashLedgerRows,
        array $operationalPerformanceDataset,
        array $period,
    ): array {
        return [
            'stock_status_donut' => $this->stockStatusDonut->build(
                $inventorySummary,
                $period['to'],
            ),
            'top_selling_bar' => $this->topSellingBar->build(
                $topSellingRows,
                $period['from'],
                $period['to'],
            ),
            'cashflow_line' => $this->cashflowLine->build(
                $cashLedgerRows,
                $period['from'],
                $period['to'],
            ),
            'operational_performance_bar' => $this->operationalPerformanceBar->build(
                is_array($operationalPerformanceDataset['period_rows'] ?? null)
                    ? $operationalPerformanceDataset['period_rows']
                    : [],
                is_array($operationalPerformanceDataset['summary'] ?? null)
                    ? $operationalPerformanceDataset['summary']
                    : [],
                $period['from'],
                $period['to'],
            ),
        ];
    }
}
