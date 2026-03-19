<?php

declare(strict_types=1);

namespace App\Adapters\Out\Procurement;

use App\Ports\Out\Procurement\ProcurementInvoiceDetailReaderPort;
use Illuminate\Support\Facades\DB;

final class DatabaseProcurementInvoiceDetailReaderAdapter implements ProcurementInvoiceDetailReaderPort
{
    public function getById(string $supplierInvoiceId): ?array
    {
        $paymentTotalsSubquery = DB::table('supplier_payments')
            ->selectRaw('supplier_invoice_id, COALESCE(SUM(amount_rupiah), 0) as total_paid_rupiah')
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

        $summary = DB::table('supplier_invoices')
            ->join('suppliers', 'suppliers.id', '=', 'supplier_invoices.supplier_id')
            ->leftJoinSub($paymentTotalsSubquery, 'payment_totals', function ($join): void {
                $join->on('payment_totals.supplier_invoice_id', '=', 'supplier_invoices.id');
            })
            ->leftJoinSub($receiptCountSubquery, 'receipt_counts', function ($join): void {
                $join->on('receipt_counts.supplier_invoice_id', '=', 'supplier_invoices.id');
            })
            ->leftJoinSub($receivedQtySubquery, 'received_qty_totals', function ($join): void {
                $join->on('received_qty_totals.supplier_invoice_id', '=', 'supplier_invoices.id');
            })
            ->where('supplier_invoices.id', $supplierInvoiceId)
            ->first([
                'supplier_invoices.id as supplier_invoice_id',
                'supplier_invoices.supplier_id',
                'suppliers.nama_pt_pengirim',
                'supplier_invoices.tanggal_pengiriman as shipment_date',
                'supplier_invoices.jatuh_tempo as due_date',
                'supplier_invoices.grand_total_rupiah',
                DB::raw('COALESCE(payment_totals.total_paid_rupiah, 0) as total_paid_rupiah'),
                DB::raw('supplier_invoices.grand_total_rupiah - COALESCE(payment_totals.total_paid_rupiah, 0) as outstanding_rupiah'),
                DB::raw('COALESCE(receipt_counts.receipt_count, 0) as receipt_count'),
                DB::raw('COALESCE(received_qty_totals.total_received_qty, 0) as total_received_qty'),
            ]);

        if ($summary === null) {
            return null;
        }

        $lines = DB::table('supplier_invoice_lines')
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

        return [
            'summary' => [
                'supplier_invoice_id' => (string) $summary->supplier_invoice_id,
                'supplier_id' => (string) $summary->supplier_id,
                'nama_pt_pengirim' => (string) $summary->nama_pt_pengirim,
                'shipment_date' => (string) $summary->shipment_date,
                'due_date' => (string) $summary->due_date,
                'grand_total_rupiah' => (int) $summary->grand_total_rupiah,
                'total_paid_rupiah' => (int) $summary->total_paid_rupiah,
                'outstanding_rupiah' => (int) $summary->outstanding_rupiah,
                'receipt_count' => (int) $summary->receipt_count,
                'total_received_qty' => (int) $summary->total_received_qty,
            ],
            'lines' => $lines,
        ];
    }
}
