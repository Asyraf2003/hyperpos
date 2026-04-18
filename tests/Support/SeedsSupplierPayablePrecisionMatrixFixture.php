<?php

declare(strict_types=1);

namespace Tests\Support;

use App\Application\Reporting\UseCases\GetSupplierPayableSummaryHandler;
use Illuminate\Support\Facades\DB;

trait SeedsSupplierPayablePrecisionMatrixFixture
{
    private function seedProduct(): void
    {
        DB::table('products')->insert([
            'id'=>'product-1','kode_barang'=>'KB-001','nama_barang'=>'Ban Luar','nama_barang_normalized'=>'ban luar',
            'merek'=>'Federal','merek_normalized'=>'federal','ukuran'=>90,'harga_jual'=>35000,
            'deleted_at'=>null,'deleted_by_actor_id'=>null,'delete_reason'=>null,
        ]);
    }

    private function seedSupplier(string $id, string $name): void
    {
        DB::table('suppliers')->insert([
            'id'=>$id,'nama_pt_pengirim'=>$name,'nama_pt_pengirim_normalized'=>strtolower($name),
            'deleted_at'=>null,'deleted_by_actor_id'=>null,'delete_reason'=>null,
        ]);
    }

    private function seedInvoice(string $id, string $supplierId, string $ship, string $due, int $grand): void
    {
        DB::table('supplier_invoices')->insert([
            'id'=>$id,'supplier_id'=>$supplierId,
            'supplier_nama_pt_pengirim_snapshot'=>(string) DB::table('suppliers')->where('id', $supplierId)->value('nama_pt_pengirim'),
            'tanggal_pengiriman'=>$ship,'jatuh_tempo'=>$due,'grand_total_rupiah'=>$grand,
        ]);
    }

    private function seedLine(string $id, string $invoiceId, int $qty, int $total, int $unit, int $lineNo = 1): void
    {
        DB::table('supplier_invoice_lines')->insert([
            'id'=>$id,'supplier_invoice_id'=>$invoiceId,'line_no'=>$lineNo,'product_id'=>'product-1',
            'product_kode_barang_snapshot'=>'KB-001','product_nama_barang_snapshot'=>'Ban Luar',
            'product_merek_snapshot'=>'Federal','product_ukuran_snapshot'=>90,
            'qty_pcs'=>$qty,'line_total_rupiah'=>$total,'unit_cost_rupiah'=>$unit,
        ]);
    }

    private function seedPayment(string $id, string $invoiceId, int $amount, string $paidAt): void
    {
        DB::table('supplier_payments')->insert([
            'id'=>$id,'supplier_invoice_id'=>$invoiceId,'amount_rupiah'=>$amount,
            'paid_at'=>$paidAt,'proof_status'=>'pending','proof_storage_path'=>null,
        ]);
    }

    private function seedReceipt(string $id, string $invoiceId, string $date): void
    {
        DB::table('supplier_receipts')->insert(['id'=>$id,'supplier_invoice_id'=>$invoiceId,'tanggal_terima'=>$date]);
    }

    private function seedReceiptLine(string $id, string $receiptId, string $invoiceLineId, int $qty): void
    {
        DB::table('supplier_receipt_lines')->insert([
            'id'=>$id,'supplier_receipt_id'=>$receiptId,'supplier_invoice_line_id'=>$invoiceLineId,'qty_diterima'=>$qty,
        ]);
    }

    private function summary(string $from, string $to, string $ref): array
    {
        return app(GetSupplierPayableSummaryHandler::class)->handle($from, $to, $ref)->data()['rows'];
    }
}
