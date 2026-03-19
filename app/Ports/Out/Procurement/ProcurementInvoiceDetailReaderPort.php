<?php

declare(strict_types=1);

namespace App\Ports\Out\Procurement;

interface ProcurementInvoiceDetailReaderPort
{
    /**
     * @return array{
     *   summary: array{
     *     supplier_invoice_id:string,
     *     supplier_id:string,
     *     nama_pt_pengirim:string,
     *     shipment_date:string,
     *     due_date:string,
     *     grand_total_rupiah:int,
     *     total_paid_rupiah:int,
     *     outstanding_rupiah:int,
     *     receipt_count:int,
     *     total_received_qty:int,
     *     policy_state:string,
     *     lock_reasons:list<string>,
     *     allowed_actions:list<string>
     *   },
     *   lines: list<array{
     *     id:string,
     *     supplier_invoice_id:string,
     *     product_id:string,
     *     kode_barang:?string,
     *     nama_barang:string,
     *     merek:string,
     *     ukuran:?int,
     *     qty_pcs:int,
     *     line_total_rupiah:int,
     *     unit_cost_rupiah:int
     *   }>
     * }|null
     */
    public function getById(string $supplierInvoiceId): ?array;
}
