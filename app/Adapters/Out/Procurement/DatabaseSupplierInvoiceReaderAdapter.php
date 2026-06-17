<?php

declare(strict_types=1);

namespace App\Adapters\Out\Procurement;

use App\Adapters\Out\Procurement\Concerns\SupplierInvoiceReaderLines;
use App\Adapters\Out\Procurement\Concerns\SupplierInvoiceReaderTaxSummary;
use App\Core\Procurement\SupplierInvoice\SupplierInvoice;
use App\Ports\Out\Procurement\SupplierInvoiceReaderPort;
use DateTimeImmutable;
use Illuminate\Support\Facades\DB;

final class DatabaseSupplierInvoiceReaderAdapter implements SupplierInvoiceReaderPort
{
    use SupplierInvoiceReaderLines;
    use SupplierInvoiceReaderTaxSummary;

    public function getById(string $supplierInvoiceId): ?SupplierInvoice
    {
        return $this->fetch($supplierInvoiceId, false);
    }

    public function getByIdForUpdate(string $supplierInvoiceId): ?SupplierInvoice
    {
        return $this->fetch($supplierInvoiceId, true);
    }

    private function fetch(string $supplierInvoiceId, bool $lock): ?SupplierInvoice
    {
        $query = DB::table('supplier_invoices')
            ->select([
                'id',
                'supplier_id',
                'supplier_nama_pt_pengirim_snapshot',
                'nomor_faktur',
                'document_kind',
                'lifecycle_status',
                'origin_supplier_invoice_id',
                'superseded_by_supplier_invoice_id',
                'tanggal_pengiriman',
                'jatuh_tempo',
                'subtotal_before_tax_rupiah',
                'tax_input',
                'tax_mode',
                'tax_rate_basis_points',
                'tax_amount_rupiah',
            ])
            ->where('id', $supplierInvoiceId);

        if ($lock) {
            $query = $query->lockForUpdate();
        }

        $invoiceRow = $query->first();

        if ($invoiceRow === null) {
            return null;
        }

        $lines = $this->supplierInvoiceLines((string) $invoiceRow->id);

        return SupplierInvoice::rehydrate(
            (string) $invoiceRow->id,
            (string) $invoiceRow->supplier_id,
            (string) ($invoiceRow->supplier_nama_pt_pengirim_snapshot ?? ''),
            (string) ($invoiceRow->nomor_faktur ?? ''),
            (string) ($invoiceRow->document_kind ?? 'invoice'),
            (string) ($invoiceRow->lifecycle_status ?? 'active'),
            $invoiceRow->origin_supplier_invoice_id !== null ? (string) $invoiceRow->origin_supplier_invoice_id : null,
            $invoiceRow->superseded_by_supplier_invoice_id !== null ? (string) $invoiceRow->superseded_by_supplier_invoice_id : null,
            new DateTimeImmutable((string) $invoiceRow->tanggal_pengiriman),
            new DateTimeImmutable((string) $invoiceRow->jatuh_tempo),
            $lines,
            $this->taxSummary($invoiceRow, $lines),
        );
    }
}
