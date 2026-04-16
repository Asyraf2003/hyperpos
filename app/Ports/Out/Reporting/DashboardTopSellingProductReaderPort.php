<?php

declare(strict_types=1);

namespace App\Ports\Out\Reporting;

interface DashboardTopSellingProductReaderPort
{
    /**
     * @return list<array{
     *   product_id:string,
     *   kode_barang:?string,
     *   nama_barang:string,
     *   sold_qty:int,
     *   gross_revenue_rupiah:int
     * }>
     */
    public function getTopSellingProducts(
        string $fromTransactionDate,
        string $toTransactionDate,
        int $limit,
    ): array;
}
