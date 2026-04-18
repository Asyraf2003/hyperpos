<?php

declare(strict_types=1);

namespace App\Application\Reporting\UseCases\Charts;

final class CashflowLineChartDatasetBuilder
{
    /**
     * @param array<string, array{cash_in:int, cash_out:int}> $periods
     * @return array<string, mixed>
     */
    public function build(array $periods, string $fromDate, string $toDate): array
    {
        $labels = array_keys($periods);
        $cashInValues = [];
        $cashOutValues = [];
        $netValues = [];
        $totalCashIn = 0;
        $totalCashOut = 0;

        foreach ($periods as $period) {
            $cashIn = $period['cash_in'];
            $cashOut = $period['cash_out'];
            $net = $cashIn - $cashOut;

            $cashInValues[] = $cashIn;
            $cashOutValues[] = $cashOut;
            $netValues[] = $net;
            $totalCashIn += $cashIn;
            $totalCashOut += $cashOut;
        }

        return [
            'title' => 'Tren Arus Kas Bulan Ini',
            'metric_unit' => 'rupiah',
            'range' => ['date_from' => $fromDate, 'date_to' => $toDate],
            'labels' => $labels,
            'series' => [
                ['key' => 'cash_in', 'label' => 'Kas Masuk', 'values' => $cashInValues],
                ['key' => 'cash_out', 'label' => 'Kas Keluar', 'values' => $cashOutValues],
                ['key' => 'net_cash_flow', 'label' => 'Net Cash Flow', 'values' => $netValues],
            ],
            'summary' => [
                'total_cash_in_rupiah' => $totalCashIn,
                'total_cash_out_rupiah' => $totalCashOut,
                'total_net_cash_flow_rupiah' => $totalCashIn - $totalCashOut,
            ],
        ];
    }
}
