<?php

declare(strict_types=1);

namespace Database\Seeders;

use Carbon\CarbonImmutable;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

final class SupplierInvoiceScenarioSeeder extends Seeder
{
    public function run(): void
    {
        $supplier = DB::table('suppliers')->select('id', 'nama_pt_pengirim')->orderBy('nama_pt_pengirim')->first();
        $products = DB::table('products')
            ->select('id', 'kode_barang', 'nama_barang', 'merek', 'ukuran')
            ->orderBy('nama_barang')
            ->limit(4)
            ->get()
            ->values();

        if ($supplier === null || $products->count() < 3) {
            $this->command?->warn('SupplierInvoiceScenarioSeeder dilewati: butuh minimal 1 supplier dan 3 product.');
            return;
        }

        $adminId = (string) (DB::table('users')->where('email', 'admin@gmail.com')->value('id') ?? '1');

        $this->seedInvoice('seed-si-editable', $supplier, [
            ['product' => $products[0], 'qty' => 2, 'unit_cost' => 12000],
            ['product' => $products[1], 'qty' => 1, 'unit_cost' => 18000],
        ], 'SI-EDIT-001', '2026-03-01', null, null, null, null, $adminId);

        $this->seedInvoice('seed-si-received', $supplier, [
            ['product' => $products[0], 'qty' => 3, 'unit_cost' => 11000],
            ['product' => $products[2], 'qty' => 2, 'unit_cost' => 17000],
        ], 'SI-RECV-001', '2026-03-02', '2026-03-03', null, null, null, $adminId);

        $this->seedInvoice('seed-si-paid-pending', $supplier, [
            ['product' => $products[1], 'qty' => 2, 'unit_cost' => 15000],
            ['product' => $products[2], 'qty' => 1, 'unit_cost' => 21000],
        ], 'SI-PAYP-001', '2026-03-04', null, '2026-03-06', 'pending', null, $adminId);

        $this->seedInvoice('seed-si-paid-uploaded', $supplier, [
            ['product' => $products[0], 'qty' => 1, 'unit_cost' => 14000],
            ['product' => $products[2], 'qty' => 2, 'unit_cost' => 16000],
        ], 'SI-PROOF-001', '2026-03-05', null, '2026-03-07', 'uploaded', '1.jpg', $adminId);

        $this->seedInvoice('seed-si-full', $supplier, [
            ['product' => $products[1], 'qty' => 2, 'unit_cost' => 13000],
            ['product' => $products[2], 'qty' => 2, 'unit_cost' => 17500],
        ], 'SI-FULL-001', '2026-03-06', '2026-03-07', '2026-03-08', 'uploaded', '2.jpg', $adminId);

        $this->command?->info('SupplierInvoiceScenarioSeeder selesai: 5 skenario nota supplier terbaru aktif.');
    }

    private function seedInvoice(
        string $invoiceId,
        object $supplier,
        array $lineDefs,
        string $nomorFaktur,
        string $shipDate,
        ?string $receiptDate,
        ?string $paidAt,
        ?string $proofStatus,
        ?string $proofFile,
        string $adminId,
    ): void {
        $paymentId = $paidAt !== null ? $invoiceId . '-payment-1' : null;
        $receiptId = $receiptDate !== null ? $invoiceId . '-receipt-1' : null;

        if ($paymentId !== null) {
            DB::table('supplier_payment_proof_attachments')->where('supplier_payment_id', $paymentId)->delete();
            DB::table('supplier_payments')->where('id', $paymentId)->delete();
        }

        if ($receiptId !== null) {
            DB::table('supplier_receipt_lines')->where('supplier_receipt_id', $receiptId)->delete();
            DB::table('supplier_receipts')->where('id', $receiptId)->delete();
        }

        DB::table('supplier_invoice_lines')->where('supplier_invoice_id', $invoiceId)->delete();
        DB::table('supplier_invoices')->where('id', $invoiceId)->delete();

        $lines = [];
        $receiptLines = [];
        $grandTotal = 0;

        foreach ($lineDefs as $index => $def) {
            $lineId = $invoiceId . '-line-' . ($index + 1);
            $lineTotal = (int) $def['qty'] * (int) $def['unit_cost'];
            $grandTotal += $lineTotal;

            $lines[] = [
                'id' => $lineId,
                'supplier_invoice_id' => $invoiceId,
                'line_no' => $index + 1,
                'product_id' => (string) $def['product']->id,
                'product_kode_barang_snapshot' => $def['product']->kode_barang,
                'product_nama_barang_snapshot' => $def['product']->nama_barang,
                'product_merek_snapshot' => $def['product']->merek,
                'product_ukuran_snapshot' => $def['product']->ukuran,
                'qty_pcs' => (int) $def['qty'],
                'line_total_rupiah' => $lineTotal,
                'unit_cost_rupiah' => (int) $def['unit_cost'],
            ];

            if ($receiptId !== null) {
                $receiptLines[] = [
                    'id' => $receiptId . '-line-' . ($index + 1),
                    'supplier_receipt_id' => $receiptId,
                    'supplier_invoice_line_id' => $lineId,
                    'qty_diterima' => (int) $def['qty'],
                ];
            }
        }

        DB::table('supplier_invoices')->insert([
            'id' => $invoiceId,
            'supplier_id' => (string) $supplier->id,
            'supplier_nama_pt_pengirim_snapshot' => (string) $supplier->nama_pt_pengirim,
            'nomor_faktur' => $nomorFaktur,
            'nomor_faktur_normalized' => mb_strtolower($nomorFaktur, 'UTF-8'),
            'document_kind' => 'invoice',
            'lifecycle_status' => 'active',
            'origin_supplier_invoice_id' => null,
            'superseded_by_supplier_invoice_id' => null,
            'tanggal_pengiriman' => $shipDate,
            'jatuh_tempo' => CarbonImmutable::parse($shipDate)->addDays(30)->format('Y-m-d'),
            'grand_total_rupiah' => $grandTotal,
            'voided_at' => null,
            'void_reason' => null,
            'last_revision_no' => 1,
        ]);

        DB::table('supplier_invoice_lines')->insert($lines);

        if ($receiptId !== null) {
            DB::table('supplier_receipts')->insert([
                'id' => $receiptId,
                'supplier_invoice_id' => $invoiceId,
                'tanggal_terima' => $receiptDate,
            ]);

            DB::table('supplier_receipt_lines')->insert($receiptLines);
        }

        if ($paymentId !== null && $proofStatus !== null) {
            $storagePath = $proofStatus === 'uploaded' && $proofFile !== null
                ? 'supplier-payment-proofs/' . $proofFile
                : null;

            DB::table('supplier_payments')->insert([
                'id' => $paymentId,
                'supplier_invoice_id' => $invoiceId,
                'amount_rupiah' => $grandTotal,
                'paid_at' => $paidAt,
                'proof_status' => $proofStatus,
                'proof_storage_path' => $storagePath,
            ]);

            if ($storagePath !== null) {
                DB::table('supplier_payment_proof_attachments')->insert([
                    'id' => $paymentId . '-proof-1',
                    'supplier_payment_id' => $paymentId,
                    'storage_path' => $storagePath,
                    'original_filename' => $proofFile,
                    'mime_type' => 'image/jpeg',
                    'file_size_bytes' => 250000,
                    'uploaded_at' => CarbonImmutable::parse($paidAt)->addHour()->format('Y-m-d H:i:s'),
                    'uploaded_by_actor_id' => $adminId,
                ]);
            }
        }
    }
}
