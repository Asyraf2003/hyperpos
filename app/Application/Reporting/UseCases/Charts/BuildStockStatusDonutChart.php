<?php

declare(strict_types=1);

namespace App\Application\Reporting\UseCases\Charts;

final class BuildStockStatusDonutChart
{
    /**
     * @param array<string, mixed> $inventorySummary
     */
    public function build(array $inventorySummary, string $snapshotDate): array
    {
        $safe = (int) ($inventorySummary['stock_safe_product_rows'] ?? 0);
        $low = (int) ($inventorySummary['stock_low_product_rows'] ?? 0);
        $critical = (int) ($inventorySummary['stock_critical_product_rows'] ?? 0);
        $unconfigured = (int) ($inventorySummary['stock_unconfigured_product_rows'] ?? 0);

        return [
            'title' => 'Distribusi Status Stok',
            'metric_unit' => 'produk',
            'snapshot_date' => $snapshotDate,
            'total_value' => $safe + $low + $critical + $unconfigured,
            'segments' => [
                [
                    'key' => 'safe',
                    'label' => 'Stok Aman',
                    'value' => $safe,
                    'color_token' => 'success',
                ],
                [
                    'key' => 'low',
                    'label' => 'Mulai Restok',
                    'value' => $low,
                    'color_token' => 'warning',
                ],
                [
                    'key' => 'critical',
                    'label' => 'Stok Kritis',
                    'value' => $critical,
                    'color_token' => 'danger',
                ],
                [
                    'key' => 'unconfigured',
                    'label' => 'Belum Diatur',
                    'value' => $unconfigured,
                    'color_token' => 'info',
                ],
            ],
        ];
    }
}
