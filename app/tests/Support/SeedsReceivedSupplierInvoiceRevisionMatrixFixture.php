<?php

declare(strict_types=1);

namespace Tests\Support;

use Illuminate\Support\Facades\DB;

trait SeedsReceivedSupplierInvoiceRevisionMatrixFixture
{
    private function seedReceivedInvoiceBase(): void
    {
        DB::table('suppliers')->insert(['id'=>'supplier-1','nama_pt_pengirim'=>'PT Sumber Makmur','nama_pt_pengirim_normalized'=>'pt sumber makmur']);
        DB::table('products')->insert(['id'=>'product-1','kode_barang'=>'KB-001','nama_barang'=>'Ban Luar','merek'=>'Federal','ukuran'=>90,'harga_jual'=>35000]);
        DB::table('supplier_invoices')->insert([
            'id'=>'invoice-1','supplier_id'=>'supplier-1','supplier_nama_pt_pengirim_snapshot'=>'PT Sumber Makmur',
            'nomor_faktur'=>'INV-SUP-001','nomor_faktur_normalized'=>'inv-sup-001','document_kind'=>'invoice',
            'lifecycle_status'=>'active','origin_supplier_invoice_id'=>null,'superseded_by_supplier_invoice_id'=>null,
            'tanggal_pengiriman'=>'2026-03-15','jatuh_tempo'=>'2026-04-14','grand_total_rupiah'=>20000,'voided_at'=>null,
            'void_reason'=>null,'last_revision_no'=>1,
        ]);
        DB::table('supplier_invoice_lines')->insert([
            'id'=>'invoice-line-1','supplier_invoice_id'=>'invoice-1','revision_no'=>1,'is_current'=>1,'source_line_id'=>null,
            'superseded_by_line_id'=>null,'superseded_at'=>null,'line_no'=>1,'product_id'=>'product-1',
            'product_kode_barang_snapshot'=>'KB-001','product_nama_barang_snapshot'=>'Ban Luar','product_merek_snapshot'=>'Federal',
            'product_ukuran_snapshot'=>90,'qty_pcs'=>2,'line_total_rupiah'=>20000,'unit_cost_rupiah'=>10000,
        ]);
        DB::table('supplier_receipts')->insert(['id'=>'receipt-1','supplier_invoice_id'=>'invoice-1','tanggal_terima'=>'2026-03-16']);
        DB::table('supplier_receipt_lines')->insert([
            'id'=>'receipt-line-1','supplier_receipt_id'=>'receipt-1','supplier_invoice_line_id'=>'invoice-line-1',
            'product_id_snapshot'=>'product-1','product_kode_barang_snapshot'=>'KB-001','product_nama_barang_snapshot'=>'Ban Luar',
            'product_merek_snapshot'=>'Federal','product_ukuran_snapshot'=>90,'unit_cost_rupiah_snapshot'=>10000,'qty_diterima'=>2,
        ]);
        DB::table('inventory_movements')->insert([
            'id'=>'movement-receipt-1','product_id'=>'product-1','movement_type'=>'stock_in','source_type'=>'supplier_receipt_line',
            'source_id'=>'receipt-line-1','tanggal_mutasi'=>'2026-03-16','qty_delta'=>2,'unit_cost_rupiah'=>10000,'total_cost_rupiah'=>20000,
        ]);
        DB::table('product_inventory')->insert(['product_id'=>'product-1','qty_on_hand'=>2]);
        DB::table('product_inventory_costing')->insert(['product_id'=>'product-1','avg_cost_rupiah'=>10000,'inventory_value_rupiah'=>20000]);
    }

    private function seedReplacementProduct(): void
    {
        DB::table('products')->insert(['id'=>'product-2','kode_barang'=>'KB-002','nama_barang'=>'Ban Dalam','merek'=>'Federal','ukuran'=>90,'harga_jual'=>38000]);
    }

    private function seedPayment(int $amount = 5000): void
    {
        DB::table('supplier_payments')->insert([
            'id'=>'payment-1','supplier_invoice_id'=>'invoice-1','amount_rupiah'=>$amount,
            'paid_at'=>'2026-03-16','proof_status'=>'pending','proof_storage_path'=>null,
        ]);
    }

    private function setProduct1Projection(int $qtyOnHand, int $inventoryValue): void
    {
        DB::table('product_inventory')->where('product_id', 'product-1')->update(['qty_on_hand' => $qtyOnHand]);
        DB::table('product_inventory_costing')->where('product_id', 'product-1')->update([
            'avg_cost_rupiah' => 10000,
            'inventory_value_rupiah' => $inventoryValue,
        ]);
    }
}
