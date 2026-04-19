<?php

declare(strict_types=1);

namespace App\Adapters\Out\Procurement;

use App\Ports\Out\Procurement\SupplierInvoiceListProjectionSourceReaderPort;
use Illuminate\Support\Facades\DB;

final class DatabaseSupplierInvoiceListProjectionSourceReaderAdapter implements SupplierInvoiceListProjectionSourceReaderPort
{
    public function findBySupplierInvoiceId(string $supplierInvoiceId): ?array
    {
        $normalizedInvoiceId = trim($supplierInvoiceId);

        if ($normalizedInvoiceId === '') {
            return null;
        }

        $paymentTotalsSubquery = DB::table('supplier_payments')
            ->leftJoin(
                'supplier_payment_reversals',
                'supplier_payment_reversals.supplier_payment_id',
                '=',
                'supplier_payments.id'
            )
            ->whereNull('supplier_payment_reversals.id')
            ->selectRaw('supplier_invoice_id, COALESCE(SUM(amount_rupiah), 0) as total_paid_rupiah')
            ->groupBy('supplier_invoice_id');

        $paymentCountSubquery = DB::table('supplier_payments')
            ->leftJoin(
                'supplier_payment_reversals',
                'supplier_payment_reversals.supplier_payment_id',
                '=',
                'supplier_payments.id'
            )
            ->whereNull('supplier_payment_reversals.id')
            ->selectRaw('supplier_invoice_id, COUNT(*) as payment_count')
            ->groupBy('supplier_invoice_id');

        $receiptCountSubquery = DB::table('supplier_receipts')
            ->selectRaw('supplier_invoice_id, COUNT(*) as receipt_count')
            ->groupBy('supplier_invoice_id');

        $receivedQtySubquery = DB::table('supplier_receipts')
            ->join('supplier_receipt_lines', 'supplier_receipt_lines.supplier_receipt_id', '=', 'supplier_receipts.id')
            ->selectRaw(
                'supplier_receipts.supplier_invoice_id, COALESCE(SUM(supplier_receipt_lines.qty_diterima), 0) as total_received_qty'
            )
            ->groupBy('supplier_receipts.supplier_invoice_id');

        $proofAttachmentCountSubquery = DB::table('supplier_payments')
            ->leftJoin(
                'supplier_payment_reversals',
                'supplier_payment_reversals.supplier_payment_id',
                '=',
                'supplier_payments.id'
            )
            ->whereNull('supplier_payment_reversals.id')
            ->leftJoin(
                'supplier_payment_proof_attachments',
                'supplier_payment_proof_attachments.supplier_payment_id',
                '=',
                'supplier_payments.id'
            )
            ->selectRaw(
                'supplier_payments.supplier_invoice_id, COUNT(supplier_payment_proof_attachments.id) as proof_attachment_count'
            )
            ->groupBy('supplier_payments.supplier_invoice_id');

        $row = DB::table('supplier_invoices')
            ->leftJoinSub($paymentTotalsSubquery, 'payment_totals', function ($join): void {
                $join->on('payment_totals.supplier_invoice_id', '=', 'supplier_invoices.id');
            })
            ->leftJoinSub($paymentCountSubquery, 'payment_counts', function ($join): void {
                $join->on('payment_counts.supplier_invoice_id', '=', 'supplier_invoices.id');
            })
            ->leftJoinSub($receiptCountSubquery, 'receipt_counts', function ($join): void {
                $join->on('receipt_counts.supplier_invoice_id', '=', 'supplier_invoices.id');
            })
            ->leftJoinSub($receivedQtySubquery, 'received_qty_totals', function ($join): void {
                $join->on('received_qty_totals.supplier_invoice_id', '=', 'supplier_invoices.id');
            })
            ->leftJoinSub($proofAttachmentCountSubquery, 'proof_attachment_counts', function ($join): void {
                $join->on('proof_attachment_counts.supplier_invoice_id', '=', 'supplier_invoices.id');
            })
            ->where('supplier_invoices.id', $normalizedInvoiceId)
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
        if ($value === null) {
            return null;
        }

        $string = trim((string) $value);

        return $string === '' ? null : $string;
    }
}
