<?php

declare(strict_types=1);

namespace App\Application\Reporting\UseCases;

final class GetAdminDashboardOverviewHandler
{
    public function __construct(
        private readonly GetTransactionReportDatasetHandler $transactionReport,
        private readonly GetTransactionCashLedgerPerNoteHandler $transactionCashLedger,
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
            $this->supplierPayable->handle($period['from'], $period['to'])
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

        return AdminDashboardOverviewPayload::fromSources(
            $transactionSummary,
            $inventorySummary,
            $operationalProfitRow,
            $supplierPayableSummary,
            $employeeDebtSummary,
            $operationalExpenseSummary,
            $todayCash,
            $monthCash,
        );
    }
}
