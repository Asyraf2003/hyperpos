<?php

declare(strict_types=1);

namespace Tests\Feature\Procurement;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

final class ProcurementInvoiceDetailPageFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login_when_accessing_procurement_invoice_detail_page(): void
    {
        $this->get(route('admin.procurement.supplier-invoices.show', ['supplierInvoiceId' => 'invoice-1']))
            ->assertRedirect(route('login'));
    }

    public function test_kasir_is_redirected_back_to_cashier_dashboard_when_accessing_procurement_invoice_detail_page(): void
    {
        $response = $this->actingAs($this->user('kasir'))
            ->get(route('admin.procurement.supplier-invoices.show', ['supplierInvoiceId' => 'invoice-1']));

        $response->assertRedirect(route('cashier.dashboard'));
        $response->assertSessionHas('error', 'Halaman admin hanya untuk role admin.');
    }

    public function test_admin_is_redirected_back_to_procurement_index_when_invoice_detail_is_not_found(): void
    {
        $response = $this->actingAs($this->user('admin'))
            ->get(route('admin.procurement.supplier-invoices.show', ['supplierInvoiceId' => 'missing-invoice']));

        $response->assertRedirect(route('admin.procurement.supplier-invoices.index'));
        $response->assertSessionHas('error', 'Nota supplier tidak ditemukan.');
    }

    public function test_admin_can_access_procurement_invoice_detail_page_as_locked_when_receipt_and_payment_exist(): void
    {
        $this->seedProduct('product-1', 'KB-001', 'Ban Luar', 'Federal', 90, 35000);
        $this->seedProduct('product-2', null, 'Aki Kering', 'GS Astra', null, 120000);
        $this->seedSupplier('supplier-1', 'PT Sumber Makmur', 'pt sumber makmur');

        $this->seedSupplierInvoice('invoice-1', 'supplier-1', '2026-03-15', '2026-04-15', 150000);

        $this->seedSupplierInvoiceLine('invoice-line-1', 'invoice-1', 'product-1', 2, 20000, 10000);
        $this->seedSupplierInvoiceLine('invoice-line-2', 'invoice-1', 'product-2', 1, 130000, 130000);

        $this->seedSupplierPayment('payment-1', 'invoice-1', 50000, '2026-03-16', 'pending');
        $this->seedSupplierPayment('payment-2', 'invoice-1', 25000, '2026-03-17', 'uploaded');

        $this->seedSupplierReceipt('receipt-1', 'invoice-1', '2026-03-16');
        $this->seedSupplierReceipt('receipt-2', 'invoice-1', '2026-03-17');

        $this->seedSupplierReceiptLine('receipt-line-1', 'receipt-1', 'invoice-line-1', 2);
        $this->seedSupplierReceiptLine('receipt-line-2', 'receipt-2', 'invoice-line-2', 1);

        $response = $this->actingAs($this->user('admin'))
            ->get(route('admin.procurement.supplier-invoices.show', ['supplierInvoiceId' => 'invoice-1']));

        $response->assertOk();

        $response->assertSee('Policy State');
        $response->assertSee('Locked');
        $response->assertSee('Allowed Actions');
        $response->assertSee('Correction / reversal');
        $response->assertSee('Lock Reasons');
        $response->assertSee('Receipt sudah tercatat');
        $response->assertSee('Payment efektif sudah tercatat');

        $response->assertSee('Ringkasan Nota');
        $response->assertSee('Line Invoice');

        $response->assertSee('invoice-1');
        $response->assertSee('PT Sumber Makmur');
        $response->assertSee('2026-03-15');
        $response->assertSee('2026-04-15');

        $response->assertSee('Rp 150.000');
        $response->assertSee('Rp 75.000');
        $response->assertSee('Receipt Count');
        $response->assertSee('2');
        $response->assertSee('Total Received Qty');
        $response->assertSee('3');

        $response->assertSee('KB-001');
        $response->assertSee('Ban Luar');
        $response->assertSee('Federal');
        $response->assertSee('90');
        $response->assertSee('Rp 10.000');
        $response->assertSee('Rp 20.000');

        $response->assertSee('Aki Kering');
        $response->assertSee('GS Astra');
        $response->assertSee('Rp 130.000');
    }

    public function test_admin_can_access_procurement_invoice_detail_page_as_editable_when_no_receipt_and_no_payment_exist(): void
    {
        $this->seedProduct('product-1', 'KB-001', 'Ban Luar', 'Federal', 90, 35000);
        $this->seedSupplier('supplier-1', 'PT Sumber Makmur', 'pt sumber makmur');

        $this->seedSupplierInvoice('invoice-1', 'supplier-1', '2026-03-15', '2026-04-15', 20000);
        $this->seedSupplierInvoiceLine('invoice-line-1', 'invoice-1', 'product-1', 2, 20000, 10000);

        $response = $this->actingAs($this->user('admin'))
            ->get(route('admin.procurement.supplier-invoices.show', ['supplierInvoiceId' => 'invoice-1']));

        $response->assertOk();

        $response->assertSee('Policy State');
        $response->assertSee('Editable');
        $response->assertSee('Allowed Actions');
        $response->assertSee('Edit invoice');
        $response->assertSee('Void invoice');
        $response->assertSee('Lock Reasons');
        $response->assertSee('Belum ada efek turunan primer.');
    }

    private function user(string $role): User
    {
        $user = User::query()->create([
            'name' => 'Test',
            'email' => $role . '@example.test',
            'password' => 'password123',
        ]);

        DB::table('actor_accesses')->insert([
            'actor_id' => (string) $user->getAuthIdentifier(),
            'role' => $role,
        ]);

        return $user;
    }

    private function seedProduct(
        string $id,
        ?string $kodeBarang,
        string $namaBarang,
        string $merek,
        ?int $ukuran,
        int $hargaJual
    ): void {
        DB::table('products')->insert([
            'id' => $id,
            'kode_barang' => $kodeBarang,
            'nama_barang' => $namaBarang,
            'merek' => $merek,
            'ukuran' => $ukuran,
            'harga_jual' => $hargaJual,
        ]);
    }

    private function seedSupplier(
        string $id,
        string $namaPtPengirim,
        string $namaPtPengirimNormalized
    ): void {
        DB::table('suppliers')->insert([
            'id' => $id,
            'nama_pt_pengirim' => $namaPtPengirim,
            'nama_pt_pengirim_normalized' => $namaPtPengirimNormalized,
        ]);
    }

    private function seedSupplierInvoice(
        string $id,
        string $supplierId,
        string $shipmentDate,
        string $dueDate,
        int $grandTotalRupiah
    ): void {
        DB::table('supplier_invoices')->insert([
            'id' => $id,
            'supplier_id' => $supplierId,
            'tanggal_pengiriman' => $shipmentDate,
            'jatuh_tempo' => $dueDate,
            'grand_total_rupiah' => $grandTotalRupiah,
        ]);
    }

    private function seedSupplierInvoiceLine(
        string $id,
        string $supplierInvoiceId,
        string $productId,
        int $qtyPcs,
        int $lineTotalRupiah,
        int $unitCostRupiah
    ): void {
        DB::table('supplier_invoice_lines')->insert([
            'id' => $id,
            'supplier_invoice_id' => $supplierInvoiceId,
            'product_id' => $productId,
            'qty_pcs' => $qtyPcs,
            'line_total_rupiah' => $lineTotalRupiah,
            'unit_cost_rupiah' => $unitCostRupiah,
        ]);
    }

    private function seedSupplierPayment(
        string $id,
        string $supplierInvoiceId,
        int $amountRupiah,
        string $paidAt,
        string $proofStatus
    ): void {
        DB::table('supplier_payments')->insert([
            'id' => $id,
            'supplier_invoice_id' => $supplierInvoiceId,
            'amount_rupiah' => $amountRupiah,
            'paid_at' => $paidAt,
            'proof_status' => $proofStatus,
            'proof_storage_path' => null,
        ]);
    }

    private function seedSupplierReceipt(
        string $id,
        string $supplierInvoiceId,
        string $tanggalTerima
    ): void {
        DB::table('supplier_receipts')->insert([
            'id' => $id,
            'supplier_invoice_id' => $supplierInvoiceId,
            'tanggal_terima' => $tanggalTerima,
        ]);
    }

    private function seedSupplierReceiptLine(
        string $id,
        string $supplierReceiptId,
        string $supplierInvoiceLineId,
        int $qtyDiterima
    ): void {
        DB::table('supplier_receipt_lines')->insert([
            'id' => $id,
            'supplier_receipt_id' => $supplierReceiptId,
            'supplier_invoice_line_id' => $supplierInvoiceLineId,
            'qty_diterima' => $qtyDiterima,
        ]);
    }
}
