<?php

declare(strict_types=1);

namespace App\Adapters\Out\Procurement;

use App\Ports\Out\Procurement\SupplierInvoiceLineReaderPort;
use Illuminate\Support\Facades\DB;

final class DatabaseSupplierInvoiceLineReaderAdapter implements SupplierInvoiceLineReaderPort
{
    /**
     * @return list<array{
     *     id: string,
     *     supplier_invoice_id: string,
     *     product_id: string,
     *     qty_pcs: int,
     *     line_total_rupiah: int,
     *     unit_cost_rupiah: int
     * }>
     */
    public function getBySupplierInvoiceId(string $supplierInvoiceId): array
    {
        $rows = DB::table('supplier_invoice_lines')
            ->select([
                'id',
                'supplier_invoice_id',
                'product_id',
                'qty_pcs',
                'line_total_rupiah',
                'unit_cost_rupiah',
            ])
            ->where('supplier_invoice_id', $supplierInvoiceId)
            ->where('is_current', true)
            ->orderBy('line_no')
            ->get();

        $lines = [];

        foreach ($rows as $row) {
            $lines[] = [
                'id' => (string) $row->id,
                'supplier_invoice_id' => (string) $row->supplier_invoice_id,
                'product_id' => (string) $row->product_id,
                'qty_pcs' => (int) $row->qty_pcs,
                'line_total_rupiah' => (int) $row->line_total_rupiah,
                'unit_cost_rupiah' => (int) $row->unit_cost_rupiah,
            ];
        }

        return $lines;
    }
}
