<?php

declare(strict_types=1);

namespace App\Application\Reporting\UseCases;

use App\Ports\Out\Reporting\DashboardTopSellingProductReaderPort;

final class AdminDashboardOverviewPayloadBuilder
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

    /**
     * @param array{today:string,from:string,to:string} $period
     */
    public function build(array $period): array
    {
        $transactionSummary = ReportingResultDataExtractor::summary(
            $this->transactionReport->handle($period['from'], $period['to'])
        );

        $inventoryResult = $this->inventoryStockValue->handle($period['from'], $period['to']);
        $inventorySummary = ReportingResultDataExtractor::summary($inventoryResult);
        $inventorySnapshotRows = ReportingResultDataExtractor::snapshotRows($inventoryResult);

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
            DashboardRestockPriorityRows::fromSnapshotRows($inventorySnapshotRows, 5),
        );
    }
}
