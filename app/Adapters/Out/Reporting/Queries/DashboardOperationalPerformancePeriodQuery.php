<?php

declare(strict_types=1);

namespace App\Adapters\Out\Reporting\Queries;

use App\Adapters\Out\Reporting\Queries\DashboardOperationalPerformance\CashInPerDayQuery;
use App\Adapters\Out\Reporting\Queries\DashboardOperationalPerformance\DashboardOperationalPerformancePeriodAmountMerger;
use App\Adapters\Out\Reporting\Queries\DashboardOperationalPerformance\DashboardOperationalPerformancePeriodProfitCalculator;
use App\Adapters\Out\Reporting\Queries\DashboardOperationalPerformance\DashboardOperationalPerformancePeriodRowMapFactory;
use App\Adapters\Out\Reporting\Queries\DashboardOperationalPerformance\EmployeeDebtCashOutPerDayQuery;
use App\Adapters\Out\Reporting\Queries\DashboardOperationalPerformance\ExternalPurchaseCostPerDayQuery;
use App\Adapters\Out\Reporting\Queries\DashboardOperationalPerformance\OperationalExpensePerDayQuery;
use App\Adapters\Out\Reporting\Queries\DashboardOperationalPerformance\PayrollDisbursementPerDayQuery;
use App\Adapters\Out\Reporting\Queries\DashboardOperationalPerformance\PotentialChangePerDayQuery;
use App\Adapters\Out\Reporting\Queries\DashboardOperationalPerformance\RefundPerDayQuery;
use App\Adapters\Out\Reporting\Queries\DashboardOperationalPerformance\StoreStockCogsPerDayQuery;
use App\Ports\Out\Reporting\DashboardOperationalPerformanceReaderPort;

/**
 * @phpstan-import-type DashboardOperationalPerformancePeriodRow from DashboardOperationalPerformanceReaderPort
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
        private readonly PotentialChangePerDayQuery $potentialChange,
        private readonly DashboardOperationalPerformancePeriodRowMapFactory $rowMapFactory,
        private readonly DashboardOperationalPerformancePeriodAmountMerger $amountMerger,
        private readonly DashboardOperationalPerformancePeriodProfitCalculator $profitCalculator,
    ) {
    }

    /**
     * @return list<DashboardOperationalPerformancePeriodRow>
     */
    public function rows(string $fromDate, string $toDate): array
    {
        $rowsByKey = $this->rowMapFactory->create($fromDate, $toDate);

        $this->amountMerger->merge($rowsByKey, $this->cashIn->rows($fromDate, $toDate), 'cash_in_rupiah');
        $this->amountMerger->merge($rowsByKey, $this->refund->rows($fromDate, $toDate), 'refund_rupiah');
        $this->amountMerger->merge($rowsByKey, $this->externalPurchase->rows($fromDate, $toDate), 'external_purchase_cost_rupiah');
        $this->amountMerger->merge($rowsByKey, $this->storeStockCogs->rows($fromDate, $toDate), 'store_stock_cogs_rupiah');
        $this->amountMerger->merge($rowsByKey, $this->operationalExpense->rows($fromDate, $toDate), 'operational_expense_rupiah');
        $this->amountMerger->merge($rowsByKey, $this->payrollDisbursement->rows($fromDate, $toDate), 'payroll_disbursement_rupiah');
        $this->amountMerger->merge($rowsByKey, $this->employeeDebtCashOut->rows($fromDate, $toDate), 'employee_debt_cash_out_rupiah');
        $this->amountMerger->merge($rowsByKey, $this->potentialChange->rows($fromDate, $toDate), 'potential_change_rupiah');

        $this->profitCalculator->calculate($rowsByKey);

        return array_values($rowsByKey);
    }
}
