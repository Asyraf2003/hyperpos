<?php

declare(strict_types=1);

namespace App\Adapters\Out\Reporting\Queries;

use App\Adapters\Out\Reporting\Queries\DashboardOperationalPerformance\CashInPerDayQuery;
use App\Adapters\Out\Reporting\Queries\DashboardOperationalPerformance\EmployeeDebtCashOutPerDayQuery;
use App\Adapters\Out\Reporting\Queries\DashboardOperationalPerformance\ExternalPurchaseCostPerDayQuery;
use App\Adapters\Out\Reporting\Queries\DashboardOperationalPerformance\OperationalExpensePerDayQuery;
use App\Adapters\Out\Reporting\Queries\DashboardOperationalPerformance\PayrollDisbursementPerDayQuery;
use App\Adapters\Out\Reporting\Queries\DashboardOperationalPerformance\RefundPerDayQuery;
use App\Adapters\Out\Reporting\Queries\DashboardOperationalPerformance\StoreStockCogsPerDayQuery;
use App\Ports\Out\Reporting\DashboardOperationalPerformanceReaderPort;
use Carbon\CarbonImmutable;

/**
 * @phpstan-import-type DashboardOperationalPerformancePeriodRow from DashboardOperationalPerformanceReaderPort
 * @phpstan-type DashboardOperationalPerformancePeriodRowsByKey array<string, DashboardOperationalPerformancePeriodRow>
 * @phpstan-type DashboardOperationalPerformanceAmountRow array{
 *   period_key:string,
 *   period_label:string,
 *   amount_rupiah:int
 * }
 */
final class DashboardOperationalPerformancePeriodQuery
{
    public function __construct(
        private readonly CashInPerDayQuery $cashIn,
        private readonly RefundPerDayQuery $refund,
        private readonly ExternalPurchaseCostPerDayQuery $externalPurchase,
        private readonly StoreStockCogsPerDayQuery $storeStockCogs,
        private readonly OperationalExpensePerDayQuery $operationalExpense,
        private readonly PayrollDisbursementPerDayQuery $payrollDisbursement,
        private readonly EmployeeDebtCashOutPerDayQuery $employeeDebtCashOut,
    ) {
    }

    /**
     * @return list<DashboardOperationalPerformancePeriodRow>
     */
    public function rows(string $fromDate, string $toDate): array
    {
        $rows = $this->emptyPeriods($fromDate, $toDate);

        $this->mergeAmount(
            $rows,
            $this->cashIn->rows($fromDate, $toDate),
            'cash_in_rupiah',
        );

        $this->mergeAmount(
            $rows,
            $this->refund->rows($fromDate, $toDate),
            'refund_rupiah',
        );

        $this->mergeAmount(
            $rows,
            $this->externalPurchase->rows($fromDate, $toDate),
            'external_purchase_cost_rupiah',
        );

        $this->mergeAmount(
            $rows,
            $this->storeStockCogs->rows($fromDate, $toDate),
            'store_stock_cogs_rupiah',
        );

        $this->mergeAmount(
            $rows,
            $this->operationalExpense->rows($fromDate, $toDate),
            'operational_expense_rupiah',
        );

        $this->mergeAmount(
            $rows,
            $this->payrollDisbursement->rows($fromDate, $toDate),
            'payroll_disbursement_rupiah',
        );

        $this->mergeAmount(
            $rows,
            $this->employeeDebtCashOut->rows($fromDate, $toDate),
            'employee_debt_cash_out_rupiah',
        );

        foreach ($rows as $key => $row) {
            $rows[$key]['operational_profit_rupiah'] =
                $row['cash_in_rupiah']
                - $row['refund_rupiah']
                - $row['external_purchase_cost_rupiah']
                - $row['store_stock_cogs_rupiah']
                - $row['operational_expense_rupiah']
                - $row['payroll_disbursement_rupiah']
                - $row['employee_debt_cash_out_rupiah'];
        }

        return array_values($rows);
    }

    /**
     * @return DashboardOperationalPerformancePeriodRowsByKey
     */
    private function emptyPeriods(string $fromDate, string $toDate): array
    {
        $cursor = CarbonImmutable::parse($fromDate);
        $end = CarbonImmutable::parse($toDate);
        $rows = [];

        while ($cursor->lte($end)) {
            $date = $cursor->toDateString();

            $rows[$date] = [
                'period_key' => $date,
                'period_label' => $date,
                'cash_in_rupiah' => 0,
                'refund_rupiah' => 0,
                'external_purchase_cost_rupiah' => 0,
                'store_stock_cogs_rupiah' => 0,
                'operational_expense_rupiah' => 0,
                'payroll_disbursement_rupiah' => 0,
                'employee_debt_cash_out_rupiah' => 0,
                'operational_profit_rupiah' => 0,
            ];

            $cursor = $cursor->addDay();
        }

        return $rows;
    }

    /**
     * @param DashboardOperationalPerformancePeriodRowsByKey $rows
     * @param list<DashboardOperationalPerformanceAmountRow> $amountRows
     * @param 'cash_in_rupiah'|'refund_rupiah'|'external_purchase_cost_rupiah'|'store_stock_cogs_rupiah'|'operational_expense_rupiah'|'payroll_disbursement_rupiah'|'employee_debt_cash_out_rupiah' $field
     */
    private function mergeAmount(array &$rows, array $amountRows, string $field): void
    {
        foreach ($amountRows as $amountRow) {
            $periodKey = $amountRow['period_key'];

            if (! isset($rows[$periodKey])) {
                continue;
            }

            $rows[$periodKey][$field] = $amountRow['amount_rupiah'];
        }
    }
}
