<?php

declare(strict_types=1);

namespace App\Application\Reporting\UseCases\Concerns;

trait FormatsAdminDashboardTopSellingRows
{
    private static function topSellingRows(array $rows): array
    {
        return array_values(array_map(
            static fn (array $row): array => [
                'product_id' => (string) ($row['product_id'] ?? ''),
                'kode_barang' => array_key_exists('kode_barang', $row) && $row['kode_barang'] !== null
                    ? (string) $row['kode_barang']
                    : null,
                'nama_barang' => (string) ($row['nama_barang'] ?? '-'),
                'sold_qty' => (int) ($row['sold_qty'] ?? 0),
                'gross_revenue_rupiah' => (int) ($row['gross_revenue_rupiah'] ?? 0),
            ],
            $rows
        ));
    }
}
