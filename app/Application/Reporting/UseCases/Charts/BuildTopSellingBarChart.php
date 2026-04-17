<?php

declare(strict_types=1);

namespace App\Application\Reporting\UseCases\Charts;

final class BuildTopSellingBarChart
{
    /**
     * @param list<array{
     *   product_id:string,
     *   kode_barang:?string,
     *   nama_barang:string,
     *   sold_qty:int,
     *   gross_revenue_rupiah:int
     * }> $rows
     */
    public function build(array $rows, string $fromDate, string $toDate): array
    {
        $categories = [];
        $values = [];
        $detail = [];

        foreach ($rows as $row) {
            $productId = (string) ($row['product_id'] ?? '');
            $code = isset($row['kode_barang']) ? (string) $row['kode_barang'] : null;
            $label = (string) ($row['nama_barang'] ?? '');
            $soldQty = (int) ($row['sold_qty'] ?? 0);
            $grossRevenue = (int) ($row['gross_revenue_rupiah'] ?? 0);

            $categories[] = [
                'id' => $productId,
                'code' => $code,
                'label' => $label,
            ];

            $values[] = $soldQty;

            $detail[] = [
                'id' => $productId,
                'code' => $code,
                'label' => $label,
                'sold_qty' => $soldQty,
                'gross_revenue_rupiah' => $grossRevenue,
            ];
        }

        return [
            'title' => 'Top Produk Terjual Bulan Ini',
            'metric_unit' => 'unit',
            'range' => [
                'date_from' => $fromDate,
                'date_to' => $toDate,
            ],
            'categories' => $categories,
            'series' => [
                [
                    'key' => 'sold_qty',
                    'label' => 'Qty Terjual',
                    'values' => $values,
                ],
            ],
            'detail' => $detail,
        ];
    }
}
