<?php

declare(strict_types=1);

namespace App\Application\Reporting\UseCases;

use App\Ports\Out\Reporting\DashboardTopSellingProductReaderPort;
use Illuminate\Support\Facades\Cache;

final class GetAdminDashboardOverviewHandler
{
    public function __construct(
        private readonly GetTransactionReportDatasetHandler $transactionReport,
        private readonly GetTransactionCashLedgerPerNoteHandler $transactionCashLedger,
        private readonly DashboardTopSellingProductReaderPort $topSellingProducts,
        private readonly GetInventoryStockValueReportDatasetHandler $inventoryStockValue,
        private readonly GetOperationalProfitSummaryHandler $operationalProfit,
        private readonly GetSupplierPayableReportDatasetHandler $supplierPayable,
        private readonly GetEmployeeDebtReportDatasetHandler $employeeDebt,
        private readonly GetOperationalExpenseReportDatasetHandler $operationalExpense,
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
            return $this->buildPayload($period);
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
            fn (): array => $this->buildPayload($period),
        );
    }

    /**
     * @param array{today:string,from:string,to:string} $period
     */
    private function buildPayload(array $period): array
    {
        $transactionSummary = ReportingResultDataExtractor::summary(
            $this->transactionReport->handle($period['from'], $period['to'])
        );

        $inventorySummary = ReportingResultDataExtractor::summary(
            $this->inventoryStockValue->handle($period['from'], $period['to'])
        );

        $operationalProfitRow = ReportingResultDataExtractor::row(
            $this->operationalProfit->handle($period['from'], $period['to'])
        );

        $supplierPayableSummary = ReportingResultDataExtractor::summary(
            $this->supplierPayable->handle($period['from'], $period['to'], $period['today'])
        );

        $employeeDebtSummary = ReportingResultDataExtractor::summary(
            $this->employeeDebt->handle($period['from'], $period['to'])
        );

        $operationalExpenseSummary = ReportingResultDataExtractor::summary(
            $this->operationalExpense->handle($period['from'], $period['to'])
        );

        $todayCash = DashboardCashLedgerTotals::fromReportResult(
            $this->transactionCashLedger->handle($period['today'], $period['today'])
        );

        $monthCash = DashboardCashLedgerTotals::fromReportResult(
            $this->transactionCashLedger->handle($period['from'], $period['to'])
        );

        $topSellingRows = $this->topSellingProducts->getTopSellingProducts(
            $period['from'],
            $period['to'],
            5,
        );

        return AdminDashboardOverviewPayload::fromSources(
            $transactionSummary,
            $inventorySummary,
            $operationalProfitRow,
            $supplierPayableSummary,
            $employeeDebtSummary,
            $operationalExpenseSummary,
            $todayCash,
            $monthCash,
            $topSellingRows,
        );
    }
}
