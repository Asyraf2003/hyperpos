<?php

declare(strict_types=1);

namespace App\Ports\Out\Procurement;

interface SupplierListProjectionSourceReaderPort
{
    /**
     * @return array{
     *   supplier_id: string,
     *   nama_pt_pengirim: string,
     *   invoice_count: int,
     *   outstanding_rupiah: int,
     *   invoice_unpaid_count: int,
     *   last_shipment_date: ?string
     * }|null
     */
    public function findBySupplierId(string $supplierId): ?array;
}
