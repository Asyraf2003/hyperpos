<?php

declare(strict_types=1);

namespace App\Application\Reporting\UseCases;

use Carbon\CarbonImmutable;

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
        [$monthFrom, $monthTo] = $this->currentMonthRange();
        $today = CarbonImmutable::today()->toDateString();

        $transactionSummary = $this->extractSummary(
            $this->transactionReport->handle($monthFrom, $monthTo)
        );

        $inventorySummary = $this->extractSummary(
            $this->inventoryStockValue->handle($monthFrom, $monthTo)
        );

        $operationalProfitRow = $this->extractRow(
            $this->operationalProfit->handle($monthFrom, $monthTo)
        );

        $supplierPayableSummary = $this->extractSummary(
            $this->supplierPayable->handle($monthFrom, $monthTo)
        );

        $employeeDebtSummary = $this->extractSummary(
            $this->employeeDebt->handle($monthFrom, $monthTo)
        );

        $operationalExpenseSummary = $this->extractSummary(
            $this->operationalExpense->handle($monthFrom, $monthTo)
        );

        $todayCash = $this->sumLedger(
            $this->extractRows($this->transactionCashLedger->handle($today, $today))
        );

        $monthCash = $this->sumLedger(
            $this->extractRows($this->transactionCashLedger->handle($monthFrom, $monthTo))
        );

        return [
            'hero' => [
                'monthly_gross_transaction_rupiah' => (int) ($transactionSummary['gross_transaction_rupiah'] ?? 0),
                'monthly_net_cash_collected_rupiah' => (int) ($transactionSummary['net_cash_collected_rupiah'] ?? 0),
                'monthly_outstanding_rupiah' => (int) ($transactionSummary['outstanding_rupiah'] ?? 0),
            ],
            'stats' => [
                'total_qty_on_hand' => (int) ($inventorySummary['total_qty_on_hand'] ?? 0),
                'total_inventory_value_rupiah' => (int) ($inventorySummary['total_inventory_value_rupiah'] ?? 0),
                'daily_cash_in_rupiah' => (int) ($todayCash['total_in_rupiah'] ?? 0),
                'monthly_net_operational_profit_rupiah' => (int) ($operationalProfitRow['net_operational_profit_rupiah'] ?? 0),
            ],
            'finance' => [
                'monthly_cash_in_rupiah' => (int) ($monthCash['total_in_rupiah'] ?? 0),
                'monthly_cash_out_rupiah' => (int) ($monthCash['total_out_rupiah'] ?? 0),
                'monthly_gross_revenue_rupiah' => (int) ($operationalProfitRow['gross_revenue_rupiah'] ?? 0),
                'monthly_net_cash_flow_rupiah' => (int) (($monthCash['total_in_rupiah'] ?? 0) - ($monthCash['total_out_rupiah'] ?? 0)),
            ],
            'position' => [
                'inventory_value_rupiah' => (int) ($inventorySummary['total_inventory_value_rupiah'] ?? 0),
                'transaction_outstanding_rupiah' => (int) ($transactionSummary['outstanding_rupiah'] ?? 0),
                'supplier_outstanding_rupiah' => (int) ($supplierPayableSummary['outstanding_rupiah'] ?? 0),
                'employee_debt_remaining_rupiah' => (int) ($employeeDebtSummary['total_remaining_balance'] ?? 0),
                'monthly_refunded_rupiah' => (int) ($transactionSummary['refunded_rupiah'] ?? 0),
                'monthly_operational_expense_rupiah' => (int) ($operationalExpenseSummary['total_amount_rupiah'] ?? 0),
            ],
        ];
    }

    private function currentMonthRange(): array
    {
        $today = CarbonImmutable::today();

        return [
            $today->startOfMonth()->toDateString(),
            $today->endOfMonth()->toDateString(),
        ];
    }

    private function extractSummary(object $result): array
    {
        $data = method_exists($result, 'data') ? $result->data() : null;

        return is_array($data) && is_array($data['summary'] ?? null)
            ? $data['summary']
            : [];
    }

    private function extractRow(object $result): array
    {
        $data = method_exists($result, 'data') ? $result->data() : null;

        return is_array($data) && is_array($data['row'] ?? null)
            ? $data['row']
            : [];
    }

    private function extractRows(object $result): array
    {
        $data = method_exists($result, 'data') ? $result->data() : null;

        return is_array($data) && is_array($data['rows'] ?? null)
            ? $data['rows']
            : [];
    }

    private function sumLedger(array $rows): array
    {
        $totalIn = 0;
        $totalOut = 0;

        foreach ($rows as $row) {
            $direction = (string) ($row['direction'] ?? '');
            $amount = (int) ($row['event_amount_rupiah'] ?? 0);

            if ($direction === 'in') {
                $totalIn += $amount;
                continue;
            }

            if ($direction === 'out') {
                $totalOut += $amount;
            }
        }

        return [
            'total_in_rupiah' => $totalIn,
            'total_out_rupiah' => $totalOut,
        ];
    }
}
