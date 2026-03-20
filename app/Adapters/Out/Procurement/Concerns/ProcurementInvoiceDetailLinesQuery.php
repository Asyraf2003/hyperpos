<?php

declare(strict_types=1);

namespace App\Adapters\Out\Procurement\Concerns;

use Illuminate\Support\Facades\DB;

trait ProcurementInvoiceDetailLinesQuery
{
    /**
     * @return list<array{
     *   id:string,
     *   supplier_invoice_id:string,
     *   product_id:string,
     *   kode_barang:?string,
     *   nama_barang:string,
     *   merek:string,
     *   ukuran:?int,
     *   qty_pcs:int,
     *   line_total_rupiah:int,
     *   unit_cost_rupiah:int
     * }>
     */
    private function getLineRows(string $supplierInvoiceId): array
    {
        return DB::table('supplier_invoice_lines')
            ->join('products', 'products.id', '=', 'supplier_invoice_lines.product_id')
            ->where('supplier_invoice_lines.supplier_invoice_id', $supplierInvoiceId)
            ->orderBy('supplier_invoice_lines.id')
            ->get([
                'supplier_invoice_lines.id',
                'supplier_invoice_lines.supplier_invoice_id',
                'supplier_invoice_lines.product_id',
                'products.kode_barang',
                'products.nama_barang',
                'products.merek',
                'products.ukuran',
                'supplier_invoice_lines.qty_pcs',
                'supplier_invoice_lines.line_total_rupiah',
                'supplier_invoice_lines.unit_cost_rupiah',
            ])
            ->map(static fn (object $row): array => [
                'id' => (string) $row->id,
                'supplier_invoice_id' => (string) $row->supplier_invoice_id,
                'product_id' => (string) $row->product_id,
                'kode_barang' => $row->kode_barang !== null ? (string) $row->kode_barang : null,
                'nama_barang' => (string) $row->nama_barang,
                'merek' => (string) $row->merek,
                'ukuran' => $row->ukuran !== null ? (int) $row->ukuran : null,
                'qty_pcs' => (int) $row->qty_pcs,
                'line_total_rupiah' => (int) $row->line_total_rupiah,
                'unit_cost_rupiah' => (int) $row->unit_cost_rupiah,
            ])
            ->all();
    }
}
