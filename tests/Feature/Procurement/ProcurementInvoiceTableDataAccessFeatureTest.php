<?php

declare(strict_types=1);

namespace Tests\Feature\Procurement;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

final class ProcurementInvoiceTableDataAccessFeatureTest extends TestCase
{
    use RefreshDatabase;

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
        $this->seedSupplier('supplier-1', 'PT Federal Abadi');
        $this->seedInvoice('invoice-1', 'supplier-1', '2026-03-15', '2026-04-15', 100000);
        $this->seedPayment('payment-1', 'invoice-1', 40000, '2026-03-16', 'pending');
        $this->seedReceipt('receipt-1', 'invoice-1', '2026-03-17');
        $this->seedReceiptLine('receipt-line-1', 'receipt-1', 'invoice-line-1', 3);

        $response = $this->actingAs($this->user('admin'))
            ->get(route('admin.procurement.supplier-invoices.table'));

        $response->assertOk();
        $response->assertJsonPath('success', true);
        $response->assertJsonPath('data.rows.0.supplier_invoice_id', 'invoice-1');
        $response->assertJsonPath('data.rows.0.nama_pt_pengirim', 'PT Federal Abadi');
        $response->assertJsonPath('data.rows.0.total_paid_rupiah', 40000);
        $response->assertJsonPath('data.rows.0.outstanding_rupiah', 60000);
        $response->assertJsonPath('data.rows.0.receipt_count', 1);
        $response->assertJsonPath('data.rows.0.total_received_qty', 3);
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
        DB::table('suppliers')->insert([
            'id' => $id,
            'nama_pt_pengirim' => $namaPtPengirim,
            'nama_pt_pengirim_normalized' => mb_strtolower($namaPtPengirim),
        ]);
    }

    private function seedInvoice(string $id, string $supplierId, string $shipmentDate, string $dueDate, int $grandTotal): void
    {
        DB::table('supplier_invoices')->insert([
            'id' => $id,
            'supplier_id' => $supplierId,
            'tanggal_pengiriman' => $shipmentDate,
            'jatuh_tempo' => $dueDate,
            'grand_total_rupiah' => $grandTotal,
        ]);
    }

    private function seedPayment(string $id, string $invoiceId, int $amount, string $paidAt, string $proofStatus): void
    {
        DB::table('supplier_payments')->insert([
            'id' => $id,
            'supplier_invoice_id' => $invoiceId,
            'amount_rupiah' => $amount,
            'paid_at' => $paidAt,
            'proof_status' => $proofStatus,
            'proof_storage_path' => null,
        ]);
    }

    private function seedReceipt(string $id, string $invoiceId, string $tanggalTerima): void
    {
        DB::table('supplier_receipts')->insert([
            'id' => $id,
            'supplier_invoice_id' => $invoiceId,
            'tanggal_terima' => $tanggalTerima,
        ]);
    }

    private function seedReceiptLine(string $id, string $receiptId, string $invoiceLineId, int $qtyDiterima): void
    {
        DB::table('supplier_receipt_lines')->insert([
            'id' => $id,
            'supplier_receipt_id' => $receiptId,
            'supplier_invoice_line_id' => $invoiceLineId,
            'qty_diterima' => $qtyDiterima,
        ]);
    }
}
