<?php

declare(strict_types=1);

namespace Tests\Support;

use Illuminate\Support\Facades\DB;

trait SeedsMinimalProcurementFixture
{
    private function seedMinimalProduct(
        string $id,
        ?string $kodeBarang,
        string $namaBarang,
        string $merek,
        ?int $ukuran,
        int $hargaJual
    ): void {
        DB::table('products')->updateOrInsert(
            ['id' => $id],
            [
                'kode_barang' => $kodeBarang,
                'nama_barang' => $namaBarang,
                'nama_barang_normalized' => mb_strtolower(trim($namaBarang)),
                'merek' => $merek,
                'merek_normalized' => mb_strtolower(trim($merek)),
                'ukuran' => $ukuran,
                'harga_jual' => $hargaJual,
                'deleted_at' => null,
                'deleted_by_actor_id' => null,
                'delete_reason' => null,
            ]
        );
    }

    private function seedMinimalSupplier(
        string $id,
        string $namaPtPengirim,
        string $namaPtPengirimNormalized
    ): void {
        DB::table('suppliers')->updateOrInsert(
            ['id' => $id],
            [
                'nama_pt_pengirim' => $namaPtPengirim,
                'nama_pt_pengirim_normalized' => $namaPtPengirimNormalized,
                'deleted_at' => null,
                'deleted_by_actor_id' => null,
                'delete_reason' => null,
            ]
        );
    }


    private function seedMinimalSupplierInvoice(
        string $id,
        string $supplierId,
        string $tanggalPengiriman,
        string $jatuhTempo,
        int $grandTotalRupiah,
        ?string $supplierNamaPtPengirimSnapshot = 'PT Sumber Makmur',
        ?string $nomorFaktur = null
    ): void {
        $resolvedNomorFaktur = $nomorFaktur ?? ('INV-' . strtoupper($id));

        DB::table('supplier_invoices')->updateOrInsert(
            ['id' => $id],
            [
                'supplier_id' => $supplierId,
                'supplier_nama_pt_pengirim_snapshot' => $supplierNamaPtPengirimSnapshot,
                'nomor_faktur' => $resolvedNomorFaktur,
                'nomor_faktur_normalized' => mb_strtolower(trim($resolvedNomorFaktur), 'UTF-8'),
                'document_kind' => 'invoice',
                'lifecycle_status' => 'active',
                'origin_supplier_invoice_id' => null,
                'superseded_by_supplier_invoice_id' => null,
                'tanggal_pengiriman' => $tanggalPengiriman,
                'jatuh_tempo' => $jatuhTempo,
                'grand_total_rupiah' => $grandTotalRupiah,
                'voided_at' => null,
                'void_reason' => null,
                'last_revision_no' => 0,
            ]
        );
    }

    private function seedMinimalSupplierInvoiceLine(
        string $id,
        string $supplierInvoiceId,
        string $productId,
        int $qtyPcs,
        int $lineTotalRupiah,
        int $unitCostRupiah,
        ?string $productKodeBarangSnapshot = 'KB-001',
        string $productNamaBarangSnapshot = 'Ban Luar',
        string $productMerekSnapshot = 'Federal',
        ?int $productUkuranSnapshot = 100,
        ?int $lineNo = null
    ): void {
        $existingLineNo = DB::table('supplier_invoice_lines')
            ->where('id', $id)
            ->value('line_no');

        $resolvedLineNo = $lineNo
            ?? ($existingLineNo !== null
                ? (int) $existingLineNo
                : ((int) (DB::table('supplier_invoice_lines')
                    ->where('supplier_invoice_id', $supplierInvoiceId)
                    ->max('line_no') ?? 0) + 1));

        DB::table('supplier_invoice_lines')->updateOrInsert(
            ['id' => $id],
            [
                'supplier_invoice_id' => $supplierInvoiceId,
                'line_no' => $resolvedLineNo,
                'product_id' => $productId,
                'product_kode_barang_snapshot' => $productKodeBarangSnapshot,
                'product_nama_barang_snapshot' => $productNamaBarangSnapshot,
                'product_merek_snapshot' => $productMerekSnapshot,
                'product_ukuran_snapshot' => $productUkuranSnapshot,
                'qty_pcs' => $qtyPcs,
                'line_total_rupiah' => $lineTotalRupiah,
                'unit_cost_rupiah' => $unitCostRupiah,
            ]
        );
    }

    private function seedMinimalSupplierPayment(
        string $id,
        string $supplierInvoiceId,
        int $amountRupiah,
        string $paidAt,
        string $proofStatus,
        ?string $proofStoragePath = null
    ): void {
        DB::table('supplier_payments')->updateOrInsert(
            ['id' => $id],
            [
                'supplier_invoice_id' => $supplierInvoiceId,
                'amount_rupiah' => $amountRupiah,
                'paid_at' => $paidAt,
                'proof_status' => $proofStatus,
                'proof_storage_path' => $proofStoragePath,
            ]
        );
    }

    private function seedMinimalSupplierReceipt(
        string $id,
        string $supplierInvoiceId,
        string $tanggalTerima
    ): void {
        DB::table('supplier_receipts')->updateOrInsert(
            ['id' => $id],
            [
                'supplier_invoice_id' => $supplierInvoiceId,
                'tanggal_terima' => $tanggalTerima,
            ]
        );
    }

    private function seedMinimalSupplierReceiptLine(
        string $id,
        string $supplierReceiptId,
        string $supplierInvoiceLineId,
        int $qtyDiterima
    ): void {
        DB::table('supplier_receipt_lines')->updateOrInsert(
            ['id' => $id],
            [
                'supplier_receipt_id' => $supplierReceiptId,
                'supplier_invoice_line_id' => $supplierInvoiceLineId,
                'qty_diterima' => $qtyDiterima,
            ]
        );
    }
}
