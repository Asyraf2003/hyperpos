<?php

declare(strict_types=1);

namespace App\Ports\Out\Procurement;

use App\Application\Procurement\DTO\ProcurementInvoiceTableQuery;

interface ProcurementInvoiceTableReaderPort
{
    /**
     * @return array{
     *   rows:list<array{
     *     supplier_invoice_id:string,
     *     nama_pt_pengirim:string,
     *     shipment_date:string,
     *     due_date:string,
     *     grand_total_rupiah:int,
     *     total_paid_rupiah:int,
     *     outstanding_rupiah:int,
     *     receipt_count:int,
     *     total_received_qty:int
     *   }>,
     *   meta:array{
     *     page:int,
     *     per_page:int,
     *     total:int,
     *     last_page:int,
     *     sort_by:string,
     *     sort_dir:string,
     *     filters:array{
     *       q:?string,
     *       shipment_date_from:?string,
     *       shipment_date_to:?string
     *     }
     *   }
     * }
     */
    public function search(ProcurementInvoiceTableQuery $query): array;
}
