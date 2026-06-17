<?php

declare(strict_types=1);

namespace App\Adapters\Out\Procurement\Concerns;

use App\Core\Procurement\SupplierInvoice\SupplierInvoiceLine;
use App\Core\Shared\ValueObjects\Money;
use Illuminate\Support\Facades\DB;

trait SupplierInvoiceReaderLines
{
    /** @return list<SupplierInvoiceLine> */
    private function supplierInvoiceLines(string $supplierInvoiceId): array
    {
        $rows = DB::table('supplier_invoice_lines')
            ->select([
                'id',
                'line_no',
                'product_id',
                'product_kode_barang_snapshot',
                'product_nama_barang_snapshot',
                'product_merek_snapshot',
                'product_ukuran_snapshot',
                'qty_pcs',
                'line_total_rupiah',
            ])
            ->where('supplier_invoice_id', $supplierInvoiceId)
            ->where('is_current', true)
            ->orderBy('line_no')
            ->get();

        $lines = [];

        foreach ($rows as $row) {
            $lines[] = SupplierInvoiceLine::rehydrate(
                (string) $row->id,
                (int) $row->line_no,
                (string) $row->product_id,
                $row->product_kode_barang_snapshot !== null ? (string) $row->product_kode_barang_snapshot : null,
                (string) $row->product_nama_barang_snapshot,
                (string) $row->product_merek_snapshot,
                $row->product_ukuran_snapshot !== null ? (int) $row->product_ukuran_snapshot : null,
                (int) $row->qty_pcs,
                Money::fromInt((int) $row->line_total_rupiah),
            );
        }

        return $lines;
    }
}
