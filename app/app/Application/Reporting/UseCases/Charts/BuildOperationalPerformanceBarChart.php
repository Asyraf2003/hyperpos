<?php

declare(strict_types=1);

namespace App\Application\Reporting\UseCases\Charts;

final class BuildOperationalPerformanceBarChart
{
    /**
     * @param list<array{
     *   period_key:string,
     *   period_label:string,
     *   operational_profit_rupiah:int,
     *   operational_expense_rupiah:int,
     *   refund_rupiah:int
     * }> $periodRows
     * @param array{
     *   total_operational_profit_rupiah:int,
     *   total_operational_expense_rupiah:int,
     *   total_refund_rupiah:int
     * } $summary
     */
    public function build(array $periodRows, array $summary, string $fromDate, string $toDate): array
    {
        $labels = [];
        $profitValues = [];
        $expenseValues = [];
        $refundValues = [];

        foreach ($periodRows as $row) {
            $labels[] = (string) ($row['period_label'] ?? '');
            $profitValues[] = (int) ($row['operational_profit_rupiah'] ?? 0);
            $expenseValues[] = (int) ($row['operational_expense_rupiah'] ?? 0);
            $refundValues[] = (int) ($row['refund_rupiah'] ?? 0);
        }

        return [
            'title' => 'Kinerja Operasional Bulan Ini',
            'metric_unit' => 'rupiah',
            'range' => [
                'date_from' => $fromDate,
                'date_to' => $toDate,
            ],
            'labels' => $labels,
            'series' => [
                [
                    'key' => 'operational_profit',
                    'label' => 'Laba Operasional',
                    'values' => $profitValues,
                ],
                [
                    'key' => 'operational_expense',
                    'label' => 'Biaya Operasional',
                    'values' => $expenseValues,
                ],
                [
                    'key' => 'refund',
                    'label' => 'Refund',
                    'values' => $refundValues,
                ],
            ],
            'summary' => [
                'total_operational_profit_rupiah' => (int) ($summary['total_operational_profit_rupiah'] ?? 0),
                'total_operational_expense_rupiah' => (int) ($summary['total_operational_expense_rupiah'] ?? 0),
                'total_refund_rupiah' => (int) ($summary['total_refund_rupiah'] ?? 0),
            ],
        ];
    }
}
