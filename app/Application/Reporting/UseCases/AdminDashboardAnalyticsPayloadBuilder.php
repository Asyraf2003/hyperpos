<?php

declare(strict_types=1);

namespace App\Application\Reporting\UseCases;

use App\Application\Reporting\UseCases\Charts\BuildCashflowLineChart;
use App\Application\Reporting\UseCases\Charts\BuildOperationalPerformanceBarChart;
use App\Application\Reporting\UseCases\Charts\BuildStockStatusDonutChart;
use App\Application\Reporting\UseCases\Charts\BuildTopSellingBarChart;
use App\Ports\Out\Reporting\DashboardTopSellingProductReaderPort;

final class AdminDashboardAnalyticsPayloadBuilder
{
    public function __construct(
        private readonly GetInventoryStockValueReportDatasetHandler $inventoryStockValue,
        private readonly DashboardTopSellingProductReaderPort $topSellingProducts,
        private readonly GetTransactionCashLedgerPerNoteHandler $transactionCashLedger,
        private readonly GetDashboardOperationalPerformanceDatasetHandler $operationalPerformance,
        private readonly BuildStockStatusDonutChart $stockStatusDonut,
        private readonly BuildTopSellingBarChart $topSellingBar,
        private readonly BuildCashflowLineChart $cashflowLine,
        private readonly BuildOperationalPerformanceBarChart $operationalPerformanceBar,
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
        $inventoryResult = $this->inventoryStockValue->handle(
            $period['from'],
            $period['to'],
        );

        $inventorySummary = ReportingResultDataExtractor::summary($inventoryResult);

        $topSellingRows = $this->topSellingProducts->getTopSellingProducts(
            $period['from'],
            $period['to'],
            5,
        );

        $cashLedgerResult = $this->transactionCashLedger->handle(
            $period['from'],
            $period['to'],
        );

        $cashLedgerRows = ReportingResultDataExtractor::rows($cashLedgerResult);

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
            'charts' => [
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
            ],
        ];
    }
}
