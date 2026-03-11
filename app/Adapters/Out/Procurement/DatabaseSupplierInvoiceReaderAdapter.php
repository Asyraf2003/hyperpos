<?php

declare(strict_types=1);

namespace App\Adapters\Out\Procurement;

use App\Core\Procurement\SupplierInvoice\SupplierInvoice;
use App\Core\Procurement\SupplierInvoice\SupplierInvoiceLine;
use App\Core\Shared\ValueObjects\Money;
use App\Ports\Out\Procurement\SupplierInvoiceReaderPort;
use DateTimeImmutable;
use Illuminate\Support\Facades\DB;

final class DatabaseSupplierInvoiceReaderAdapter implements SupplierInvoiceReaderPort
{
    public function getById(string $supplierInvoiceId): ?SupplierInvoice
    {
        $invoiceRow = DB::table('supplier_invoices')
            ->select(['id', 'supplier_id', 'tanggal_pengiriman'])
            ->where('id', $supplierInvoiceId)
            ->first();

        if ($invoiceRow === null) {
            return null;
        }

        $lineRows = DB::table('supplier_invoice_lines')
            ->select([
                'id',
                'supplier_invoice_id',
                'product_id',
                'qty_pcs',
                'line_total_rupiah',
            ])
            ->where('supplier_invoice_id', (string) $invoiceRow->id)
            ->get();

        $lines = [];

        foreach ($lineRows as $row) {
            $lines[] = SupplierInvoiceLine::rehydrate(
                (string) $row->id,
                (string) $row->product_id,
                (int) $row->qty_pcs,
                Money::fromInt((int) $row->line_total_rupiah),
            );
        }

        return SupplierInvoice::rehydrate(
            (string) $invoiceRow->id,
            (string) $invoiceRow->supplier_id,
            new DateTimeImmutable((string) $invoiceRow->tanggal_pengiriman),
            $lines,
        );
    }
}
