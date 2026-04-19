<?php

declare(strict_types=1);

namespace App\Adapters\Out\Procurement;

use App\Ports\Out\Procurement\SupplierInvoiceListProjectionSourceReaderPort;
use Illuminate\Support\Facades\DB;

final class DatabaseSupplierInvoiceListProjectionSourceReaderAdapter implements SupplierInvoiceListProjectionSourceReaderPort
{
    public function __construct(
        private readonly SupplierInvoiceListProjectionActivePaymentSubqueries $payments,
        private readonly SupplierInvoiceListProjectionReceiptSubqueries $receipts,
    ) {
    }

    public function findBySupplierInvoiceId(string $supplierInvoiceId): ?array
    {
        $invoiceId = trim($supplierInvoiceId);

        if ($invoiceId === '') {
            return null;
        }

        $row = DB::table('supplier_invoices')
            ->leftJoinSub($this->payments->totals(), 'payment_totals', fn ($join) => $join->on('payment_totals.supplier_invoice_id', '=', 'supplier_invoices.id'))
            ->leftJoinSub($this->payments->counts(), 'payment_counts', fn ($join) => $join->on('payment_counts.supplier_invoice_id', '=', 'supplier_invoices.id'))
            ->leftJoinSub($this->receipts->counts(), 'receipt_counts', fn ($join) => $join->on('receipt_counts.supplier_invoice_id', '=', 'supplier_invoices.id'))
            ->leftJoinSub($this->receipts->receivedQtyTotals(), 'received_qty_totals', fn ($join) => $join->on('received_qty_totals.supplier_invoice_id', '=', 'supplier_invoices.id'))
            ->leftJoinSub($this->payments->proofAttachmentCounts(), 'proof_attachment_counts', fn ($join) => $join->on('proof_attachment_counts.supplier_invoice_id', '=', 'supplier_invoices.id'))
            ->where('supplier_invoices.id', $invoiceId)
            ->first([
                'supplier_invoices.id as supplier_invoice_id',
                'supplier_invoices.supplier_id',
                'supplier_invoices.nomor_faktur',
                'supplier_invoices.nomor_faktur_normalized',
                'supplier_invoices.supplier_nama_pt_pengirim_snapshot',
                'supplier_invoices.tanggal_pengiriman as shipment_date',
                'supplier_invoices.jatuh_tempo as due_date',
                'supplier_invoices.grand_total_rupiah',
                'supplier_invoices.lifecycle_status',
                'supplier_invoices.voided_at',
                'supplier_invoices.last_revision_no',
                DB::raw('COALESCE(payment_totals.total_paid_rupiah, 0) as total_paid_rupiah'),
                DB::raw('supplier_invoices.grand_total_rupiah - COALESCE(payment_totals.total_paid_rupiah, 0) as outstanding_rupiah'),
                DB::raw('COALESCE(payment_counts.payment_count, 0) as payment_count'),
                DB::raw('COALESCE(receipt_counts.receipt_count, 0) as receipt_count'),
                DB::raw('COALESCE(received_qty_totals.total_received_qty, 0) as total_received_qty'),
                DB::raw('COALESCE(proof_attachment_counts.proof_attachment_count, 0) as proof_attachment_count'),
            ]);

        if ($row === null) {
            return null;
        }

        return [
            'supplier_invoice_id' => (string) $row->supplier_invoice_id,
            'supplier_id' => (string) $row->supplier_id,
            'nomor_faktur' => $this->nullableString($row->nomor_faktur),
            'nomor_faktur_normalized' => $this->nullableString($row->nomor_faktur_normalized),
            'supplier_nama_pt_pengirim_snapshot' => $this->nullableString($row->supplier_nama_pt_pengirim_snapshot),
            'shipment_date' => (string) $row->shipment_date,
            'due_date' => (string) $row->due_date,
            'grand_total_rupiah' => (int) $row->grand_total_rupiah,
            'total_paid_rupiah' => (int) $row->total_paid_rupiah,
            'outstanding_rupiah' => (int) $row->outstanding_rupiah,
            'payment_count' => (int) $row->payment_count,
            'receipt_count' => (int) $row->receipt_count,
            'total_received_qty' => (int) $row->total_received_qty,
            'proof_attachment_count' => (int) $row->proof_attachment_count,
            'lifecycle_status' => (string) $row->lifecycle_status,
            'voided_at' => $this->nullableString($row->voided_at),
            'last_revision_no' => (int) $row->last_revision_no,
        ];
    }

    private function nullableString(mixed $value): ?string
    {
        $string = $value === null ? '' : trim((string) $value);

        return $string === '' ? null : $string;
    }
}
