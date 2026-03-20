<?php

declare(strict_types=1);

namespace App\Adapters\Out\Procurement;

use App\Core\Procurement\SupplierInvoice\SupplierInvoice;
use App\Core\Procurement\SupplierInvoice\SupplierInvoiceLine;
use App\Ports\Out\Procurement\SupplierInvoiceWriterPort;
use Illuminate\Support\Facades\DB;

final class DatabaseSupplierInvoiceWriterAdapter implements SupplierInvoiceWriterPort
{
    public function create(SupplierInvoice $supplierInvoice): void
    {
        DB::table('supplier_invoices')->insert($this->toInvoiceRecord($supplierInvoice));

        DB::table('supplier_invoice_lines')->insert($this->toLineRecords($supplierInvoice));
    }

    /**
     * @return array<string, string|int>
     */
    private function toInvoiceRecord(SupplierInvoice $supplierInvoice): array
    {
        return [
            'id' => $supplierInvoice->id(),
            'supplier_id' => $supplierInvoice->supplierId(),
            'supplier_nama_pt_pengirim_snapshot' => $supplierInvoice->supplierNamaPtPengirimSnapshot(),
            'tanggal_pengiriman' => $supplierInvoice->tanggalPengiriman()->format('Y-m-d'),
            'jatuh_tempo' => $supplierInvoice->jatuhTempo()->format('Y-m-d'),
            'grand_total_rupiah' => $supplierInvoice->grandTotalRupiah()->amount(),
        ];
    }

    /**
     * @return list<array<string, string|int|null>>
     */
    private function toLineRecords(SupplierInvoice $supplierInvoice): array
    {
        return array_map(
            static fn (SupplierInvoiceLine $line): array => [
                'id' => $line->id(),
                'supplier_invoice_id' => $supplierInvoice->id(),
                'product_id' => $line->productId(),
                'product_kode_barang_snapshot' => $line->productKodeBarangSnapshot(),
                'product_nama_barang_snapshot' => $line->productNamaBarangSnapshot(),
                'product_merek_snapshot' => $line->productMerekSnapshot(),
                'product_ukuran_snapshot' => $line->productUkuranSnapshot(),
                'qty_pcs' => $line->qtyPcs(),
                'line_total_rupiah' => $line->lineTotalRupiah()->amount(),
                'unit_cost_rupiah' => $line->unitCostRupiah()->amount(),
            ],
            $supplierInvoice->lines(),
        );
    }
}
