<?php

declare(strict_types=1);

namespace Tests\Feature\Procurement;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Support\SeedsMinimalProcurementFixture;
use Tests\TestCase;

final class ProcurementInvoiceTableDataAccessFeatureTest extends TestCase
{
    use RefreshDatabase;
    use SeedsMinimalProcurementFixture;

    public function test_guest_is_redirected_to_login_when_accessing_procurement_invoice_table_data(): void
    {
        $this->get(route('admin.procurement.supplier-invoices.table'))
            ->assertRedirect(route('login'));
    }

    public function test_kasir_is_redirected_back_to_cashier_dashboard_when_accessing_procurement_invoice_table_data(): void
    {
        $response = $this->actingAs($this->user('kasir'))
            ->get(route('admin.procurement.supplier-invoices.table'));

        $response->assertRedirect(route('cashier.dashboard'));
        $response->assertSessionHas('error', 'Halaman admin hanya untuk role admin.');
    }

    public function test_admin_can_get_procurement_invoice_table_json(): void
    {
        $this->seedSupplier('supplier-1', 'PT Supplier Baru');
        $this->seedProductFixture();
        $this->seedInvoice('invoice-1', 'supplier-1', '2026-03-15', '2026-04-15', 100000, 'PT Federal Abadi');
        $this->seedInvoiceLine('invoice-line-1', 'invoice-1');
        $this->seedPayment('payment-1', 'invoice-1', 40000, '2026-03-16', 'pending');
        $this->seedReceipt('receipt-1', 'invoice-1', '2026-03-17');
        $this->seedReceiptLine('receipt-line-1', 'receipt-1', 'invoice-line-1', 3);

        $response = $this->actingAs($this->user('admin'))
            ->get(route('admin.procurement.supplier-invoices.table'));

        $response->assertOk();
        $response->assertJsonPath('success', true);
        $response->assertJsonPath('data.rows.0.supplier_invoice_id', 'invoice-1');
        $response->assertJsonPath('data.rows.0.supplier_nama_pt_pengirim_current', 'PT Supplier Baru');
        $response->assertJsonPath('data.rows.0.supplier_nama_pt_pengirim_snapshot', 'PT Federal Abadi');
        $response->assertJsonPath('data.rows.0.total_paid_rupiah', 40000);
        $response->assertJsonPath('data.rows.0.outstanding_rupiah', 60000);
        $response->assertJsonPath('data.rows.0.receipt_count', 1);
        $response->assertJsonPath('data.rows.0.total_received_qty', 3);
        $response->assertJsonPath('data.rows.0.policy_state', 'locked');
        $response->assertJsonPath('data.rows.0.edit_action_kind', 'revise');
        $response->assertJsonPath('data.rows.0.edit_action_label', 'Koreksi');
        $response->assertJsonPath(
            'data.rows.0.edit_action_url',
            route('admin.procurement.supplier-invoices.revise', ['supplierInvoiceId' => 'invoice-1'])
        );
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

    private function seedSupplier(string $id, string $namaPtPengirim): void
    {
        $this->seedMinimalSupplier($id, $namaPtPengirim, mb_strtolower($namaPtPengirim));
    }

    private function seedProductFixture(): void
    {
        $this->seedMinimalProduct('product-1', 'KB-001', 'Ban Luar', 'Federal', 100, 75000);
    }

    private function seedInvoice(
        string $id,
        string $supplierId,
        string $shipmentDate,
        string $dueDate,
        int $grandTotal,
        string $supplierNamaPtPengirimSnapshot = 'PT Federal Abadi'
    ): void {
        $this->seedMinimalSupplierInvoice(
            $id,
            $supplierId,
            $shipmentDate,
            $dueDate,
            $grandTotal,
            $supplierNamaPtPengirimSnapshot
        );
    }

    private function seedInvoiceLine(string $id, string $invoiceId): void
    {
        $this->seedMinimalSupplierInvoiceLine(
            $id,
            $invoiceId,
            'product-1',
            3,
            100000,
            33333,
            'KB-001',
            'Ban Luar',
            'Federal',
            100
        );
    }

    private function seedPayment(string $id, string $invoiceId, int $amount, string $paidAt, string $proofStatus): void
    {
        $this->seedMinimalSupplierPayment($id, $invoiceId, $amount, $paidAt, $proofStatus);
    }

    private function seedReceipt(string $id, string $invoiceId, string $tanggalTerima): void
    {
        $this->seedMinimalSupplierReceipt($id, $invoiceId, $tanggalTerima);
    }

    private function seedReceiptLine(string $id, string $receiptId, string $invoiceLineId, int $qtyDiterima): void
    {
        $this->seedMinimalSupplierReceiptLine($id, $receiptId, $invoiceLineId, $qtyDiterima);
    }
}
