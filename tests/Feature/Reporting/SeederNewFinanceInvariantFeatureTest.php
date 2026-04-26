<?php

declare(strict_types=1);

namespace Tests\Feature\Reporting;

use App\Core\Note\WorkItem\WorkItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

final class SeederNewFinanceInvariantFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_core_finance_money_invariants_hold_for_seed_like_fixture(): void
    {
        DB::table('notes')->insert([
            'id' => 'note-fin-1',
            'customer_name' => 'Finance Invariant',
            'transaction_date' => '2026-04-02',
            'total_rupiah' => 100000,
        ]);

        DB::table('work_items')->insert([
            'id' => 'wi-fin-1',
            'note_id' => 'note-fin-1',
            'line_no' => 1,
            'transaction_type' => WorkItem::TYPE_STORE_STOCK_SALE_ONLY,
            'status' => WorkItem::STATUS_OPEN,
            'subtotal_rupiah' => 100000,
        ]);

        DB::table('customer_payments')->insert([
            'id' => 'payment-fin-1',
            'amount_rupiah' => 100000,
            'paid_at' => '2026-04-02',
        ]);

        DB::table('payment_allocations')->insert([
            'id' => 'alloc-fin-1',
            'customer_payment_id' => 'payment-fin-1',
            'note_id' => 'note-fin-1',
            'amount_rupiah' => 100000,
        ]);

        DB::table('payment_component_allocations')->insert([
            [
                'id' => 'pca-fin-1',
                'customer_payment_id' => 'payment-fin-1',
                'note_id' => 'note-fin-1',
                'work_item_id' => 'wi-fin-1',
                'component_type' => 'product_only_work_item',
                'component_ref_id' => 'wi-fin-1',
                'component_amount_rupiah_snapshot' => 60000,
                'allocated_amount_rupiah' => 60000,
                'allocation_priority' => 1,
            ],
            [
                'id' => 'pca-fin-2',
                'customer_payment_id' => 'payment-fin-1',
                'note_id' => 'note-fin-1',
                'work_item_id' => 'wi-fin-1',
                'component_type' => 'service_fee',
                'component_ref_id' => 'wi-fin-1',
                'component_amount_rupiah_snapshot' => 40000,
                'allocated_amount_rupiah' => 40000,
                'allocation_priority' => 2,
            ],
        ]);

        DB::table('customer_refunds')->insert([
            'id' => 'refund-fin-1',
            'customer_payment_id' => 'payment-fin-1',
            'note_id' => 'note-fin-1',
            'amount_rupiah' => 30000,
            'refunded_at' => '2026-04-03',
            'reason' => 'Finance invariant refund',
        ]);

        DB::table('refund_component_allocations')->insert([
            'id' => 'rca-fin-1',
            'customer_refund_id' => 'refund-fin-1',
            'customer_payment_id' => 'payment-fin-1',
            'note_id' => 'note-fin-1',
            'work_item_id' => 'wi-fin-1',
            'component_type' => 'service_fee',
            'component_ref_id' => 'wi-fin-1',
            'refunded_amount_rupiah' => 30000,
            'refund_priority' => 1,
        ]);

        DB::table('products')->insert([
            'id' => 'product-fin-1',
            'kode_barang' => 'FIN-001',
            'nama_barang' => 'Produk Finance',
            'nama_barang_normalized' => 'produk finance',
            'merek' => 'General',
            'merek_normalized' => 'general',
            'ukuran' => 100,
            'harga_jual' => 150000,
        ]);

        DB::table('suppliers')->insert([
            'id' => 'supplier-fin-1',
            'nama_pt_pengirim' => 'PT Finance',
            'nama_pt_pengirim_normalized' => 'pt finance',
        ]);

        DB::table('supplier_invoices')->insert([
            [
                'id' => 'si-fin-active',
                'nomor_faktur' => 'SI-FIN-ACTIVE',
                'nomor_faktur_normalized' => 'si-fin-active',
                'supplier_id' => 'supplier-fin-1',
                'supplier_nama_pt_pengirim_snapshot' => 'PT Finance',
                'tanggal_pengiriman' => '2026-04-02',
                'jatuh_tempo' => '2026-05-02',
                'grand_total_rupiah' => 100000,
                'voided_at' => null,
            ],
            [
                'id' => 'si-fin-voided',
                'nomor_faktur' => 'SI-FIN-VOIDED',
                'nomor_faktur_normalized' => 'si-fin-voided',
                'supplier_id' => 'supplier-fin-1',
                'supplier_nama_pt_pengirim_snapshot' => 'PT Finance',
                'tanggal_pengiriman' => '2026-04-02',
                'jatuh_tempo' => '2026-05-02',
                'grand_total_rupiah' => 40000,
                'voided_at' => '2026-04-03 10:00:00',
            ],
        ]);

        DB::table('supplier_invoice_lines')->insert([
            'id' => 'sil-fin-1',
            'supplier_invoice_id' => 'si-fin-active',
            'line_no' => 1,
            'product_id' => 'product-fin-1',
            'product_kode_barang_snapshot' => 'FIN-001',
            'product_nama_barang_snapshot' => 'Produk Finance',
            'product_merek_snapshot' => 'General',
            'product_ukuran_snapshot' => 100,
            'qty_pcs' => 2,
            'line_total_rupiah' => 100000,
            'unit_cost_rupiah' => 50000,
        ]);

        DB::table('supplier_payments')->insert([
            [
                'id' => 'sp-fin-active',
                'supplier_invoice_id' => 'si-fin-active',
                'amount_rupiah' => 70000,
                'paid_at' => '2026-04-02',
                'proof_status' => 'pending',
            ],
            [
                'id' => 'sp-fin-voided',
                'supplier_invoice_id' => 'si-fin-voided',
                'amount_rupiah' => 40000,
                'paid_at' => '2026-04-03',
                'proof_status' => 'pending',
            ],
        ]);

        $this->assertSame(
            (int) DB::table('customer_payments')->sum('amount_rupiah'),
            (int) DB::table('payment_allocations')->sum('amount_rupiah'),
        );
        $this->assertSame(
            (int) DB::table('customer_payments')->sum('amount_rupiah'),
            (int) DB::table('payment_component_allocations')->sum('allocated_amount_rupiah'),
        );
        $this->assertSame(
            (int) DB::table('customer_refunds')->sum('amount_rupiah'),
            (int) DB::table('refund_component_allocations')->sum('refunded_amount_rupiah'),
        );

        $activeInvoiceTotal = (int) DB::table('supplier_invoices')
            ->whereNull('voided_at')
            ->sum('grand_total_rupiah');

        $activeLineTotal = (int) DB::table('supplier_invoice_lines')
            ->join('supplier_invoices', 'supplier_invoices.id', '=', 'supplier_invoice_lines.supplier_invoice_id')
            ->whereNull('supplier_invoices.voided_at')
            ->sum('supplier_invoice_lines.line_total_rupiah');

        $activePaymentTotal = (int) DB::table('supplier_payments')
            ->join('supplier_invoices', 'supplier_invoices.id', '=', 'supplier_payments.supplier_invoice_id')
            ->whereNull('supplier_invoices.voided_at')
            ->sum('supplier_payments.amount_rupiah');

        $this->assertSame($activeInvoiceTotal, $activeLineTotal);
        $this->assertSame(30000, $activeInvoiceTotal - $activePaymentTotal);
    }
}
