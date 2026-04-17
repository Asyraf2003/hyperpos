<?php

declare(strict_types=1);

namespace App\Application\Reporting\UseCases;

use Carbon\CarbonImmutable;

final class AdminDashboardAnalyticsPayloadBuilder
{
    /**
     * @param array{
     *   today:string,
     *   active_month:string,
     *   from:string,
     *   to:string
     * } $period
     */
    public function build(array $period): array
    {
        $labels = $this->labels($period['from'], $period['to']);

        return [
            'period' => [
                'window_type' => 'month_to_date',
                'anchor_date' => $period['today'],
                'active_month' => $period['active_month'],
                'date_from' => $period['from'],
                'date_to' => $period['to'],
                'granularity' => 'daily',
                'generated_at' => now()->toISOString(),
            ],
            'charts' => [
                'stock_status_donut' => [
                    'title' => 'Distribusi Status Stok',
                    'metric_unit' => 'produk',
                    'snapshot_date' => $period['to'],
                    'total_value' => 0,
                    'segments' => [
                        ['key' => 'safe', 'label' => 'Stok Aman', 'value' => 0, 'color_token' => 'success'],
                        ['key' => 'low', 'label' => 'Mulai Restok', 'value' => 0, 'color_token' => 'warning'],
                        ['key' => 'critical', 'label' => 'Stok Kritis', 'value' => 0, 'color_token' => 'danger'],
                        ['key' => 'unconfigured', 'label' => 'Belum Diatur', 'value' => 0, 'color_token' => 'info'],
                    ],
                ],
                'top_selling_bar' => [
                    'title' => 'Top Produk Terjual Bulan Ini',
                    'metric_unit' => 'unit',
                    'range' => [
                        'date_from' => $period['from'],
                        'date_to' => $period['to'],
                    ],
                    'categories' => [],
                    'series' => [
                        [
                            'key' => 'sold_qty',
                            'label' => 'Qty Terjual',
                            'values' => [],
                        ],
                    ],
                    'detail' => [],
                ],
                'cashflow_line' => $this->trendChart(
                    'Tren Arus Kas Bulan Ini',
                    $period['from'],
                    $period['to'],
                    $labels,
                    [
                        'cash_in' => 'Kas Masuk',
                        'cash_out' => 'Kas Keluar',
                        'net_cash_flow' => 'Net Cash Flow',
                    ],
                    [
                        'total_cash_in_rupiah' => 0,
                        'total_cash_out_rupiah' => 0,
                        'total_net_cash_flow_rupiah' => 0,
                    ],
                ),
                'operational_performance_bar' => $this->trendChart(
                    'Kinerja Operasional Bulan Ini',
                    $period['from'],
                    $period['to'],
                    $labels,
                    [
                        'operational_profit' => 'Laba Operasional',
                        'operational_expense' => 'Biaya Operasional',
                        'refund' => 'Refund',
                    ],
                    [
                        'total_operational_profit_rupiah' => 0,
                        'total_operational_expense_rupiah' => 0,
                        'total_refund_rupiah' => 0,
                    ],
                ),
            ],
        ];
    }

    private function trendChart(
        string $title,
        string $from,
        string $to,
        array $labels,
        array $seriesLabels,
        array $summary,
    ): array {
        $series = [];
        $zeroes = array_fill(0, count($labels), 0);

        foreach ($seriesLabels as $key => $label) {
            $series[] = [
                'key' => $key,
                'label' => $label,
                'values' => $zeroes,
            ];
        }

        return [
            'title' => $title,
            'metric_unit' => 'rupiah',
            'range' => [
                'date_from' => $from,
                'date_to' => $to,
            ],
            'labels' => $labels,
            'series' => $series,
            'summary' => $summary,
        ];
    }

    /**
     * @return list<string>
     */
    private function labels(string $from, string $to): array
    {
        $cursor = CarbonImmutable::parse($from);
        $end = CarbonImmutable::parse($to);
        $labels = [];

        while ($cursor->lte($end)) {
            $labels[] = $cursor->toDateString();
            $cursor = $cursor->addDay();
        }

        return $labels;
    }
}
