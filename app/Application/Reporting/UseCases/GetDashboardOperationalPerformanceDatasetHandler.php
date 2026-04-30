<?php

declare(strict_types=1);

namespace App\Application\Reporting\UseCases;

use App\Application\Reporting\Services\CashChangeDenominationCalculator;
use App\Ports\Out\Reporting\DashboardOperationalPerformanceReaderPort;

/**
 * @phpstan-import-type DashboardOperationalPerformancePeriodRow from DashboardOperationalPerformanceReaderPort
 */
final class GetDashboardOperationalPerformanceDatasetHandler
{
    public function __construct(
        private readonly DashboardOperationalPerformanceReaderPort $sourceReader,
        private readonly CashChangeDenominationCalculator $cashChangeDenominations,
    ) {
    }

    /**
     * @return array{
     *   period_rows:list<array{
     *     period_key:string,
     *     period_label:string,
     *     operational_profit_rupiah:int,
     *     operational_expense_rupiah:int,
     *     refund_rupiah:int,
     *     potential_change_rupiah:int
     *   }>,
     *   summary:array{
     *     total_operational_profit_rupiah:int,
     *     total_operational_expense_rupiah:int,
     *     total_refund_rupiah:int,
     *     total_potential_change_rupiah:int
     *   },
     *   cash_change_denominations:list<array{
     *     denomination:int,
     *     count:int,
     *     total_rupiah:int
     *   }>
     * }
     */
    public function handle(string $fromDate, string $toDate): array
    {
        $rows = $this->sourceReader->getOperationalPerformancePeriodRows(
            $fromDate,
            $toDate,
        );

        $periodRows = [];

        foreach ($rows as $row) {
            $periodRows[] = [
                'period_key' => $row['period_key'],
                'period_label' => $row['period_label'],
                'operational_profit_rupiah' => $row['operational_profit_rupiah'],
                'operational_expense_rupiah' => $row['operational_expense_rupiah'],
                'refund_rupiah' => $row['refund_rupiah'],
                'potential_change_rupiah' => $row['potential_change_rupiah'],
            ];
        }

        return [
            'period_rows' => $periodRows,
            'summary' => [
                'total_operational_profit_rupiah' => $this->sum($rows, 'operational_profit_rupiah'),
                'total_operational_expense_rupiah' => $this->sum($rows, 'operational_expense_rupiah'),
                'total_refund_rupiah' => $this->sum($rows, 'refund_rupiah'),
                'total_potential_change_rupiah' => $this->sum($rows, 'potential_change_rupiah'),
            ],
            'cash_change_denominations' => $this->cashChangeDenominations->aggregate(
                $this->sourceReader->getPotentialChangeAmountsRupiah($fromDate, $toDate),
            ),
        ];
    }

    /**
     * @param list<DashboardOperationalPerformancePeriodRow> $rows
     * @param 'operational_profit_rupiah'|'operational_expense_rupiah'|'refund_rupiah'|'potential_change_rupiah' $field
     */
    private function sum(array $rows, string $field): int
    {
        $total = 0;

        foreach ($rows as $row) {
            $total += $row[$field];
        }

        return $total;
    }
}
