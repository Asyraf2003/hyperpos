<?php

declare(strict_types=1);

namespace App\Application\Reporting\UseCases\Charts;

use Carbon\CarbonImmutable;

final class BuildCashflowLineChart
{
    /**
     * @param list<array{
     *   note_id:string,
     *   event_date:string,
     *   event_type:string,
     *   direction:string,
     *   event_amount_rupiah:int,
     *   customer_payment_id:?string,
     *   refund_id:?string
     * }> $rows
     */
    public function build(array $rows, string $fromDate, string $toDate): array
    {
        $periods = $this->emptyPeriods($fromDate, $toDate);

        foreach ($rows as $row) {
            $date = (string) ($row['event_date'] ?? '');
            $direction = (string) ($row['direction'] ?? '');
            $amount = (int) ($row['event_amount_rupiah'] ?? 0);

            if ($date === '' || ! isset($periods[$date])) {
                continue;
            }

            if ($direction === 'in') {
                $periods[$date]['cash_in'] += $amount;
                continue;
            }

            if ($direction === 'out') {
                $periods[$date]['cash_out'] += $amount;
            }
        }

        $labels = array_keys($periods);
        $cashInValues = [];
        $cashOutValues = [];
        $netValues = [];
        $totalCashIn = 0;
        $totalCashOut = 0;

        foreach ($periods as $period) {
            $cashIn = (int) $period['cash_in'];
            $cashOut = (int) $period['cash_out'];
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
            'range' => [
                'date_from' => $fromDate,
                'date_to' => $toDate,
            ],
            'labels' => $labels,
            'series' => [
                [
                    'key' => 'cash_in',
                    'label' => 'Kas Masuk',
                    'values' => $cashInValues,
                ],
                [
                    'key' => 'cash_out',
                    'label' => 'Kas Keluar',
                    'values' => $cashOutValues,
                ],
                [
                    'key' => 'net_cash_flow',
                    'label' => 'Net Cash Flow',
                    'values' => $netValues,
                ],
            ],
            'summary' => [
                'total_cash_in_rupiah' => $totalCashIn,
                'total_cash_out_rupiah' => $totalCashOut,
                'total_net_cash_flow_rupiah' => $totalCashIn - $totalCashOut,
            ],
        ];
    }

    /**
     * @return array<string, array{cash_in:int, cash_out:int}>
     */
    private function emptyPeriods(string $fromDate, string $toDate): array
    {
        $cursor = CarbonImmutable::parse($fromDate);
        $end = CarbonImmutable::parse($toDate);
        $periods = [];

        while ($cursor->lte($end)) {
            $date = $cursor->toDateString();

            $periods[$date] = [
                'cash_in' => 0,
                'cash_out' => 0,
            ];

            $cursor = $cursor->addDay();
        }

        return $periods;
    }
}
