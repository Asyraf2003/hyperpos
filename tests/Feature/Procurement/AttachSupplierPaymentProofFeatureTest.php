<?php

declare(strict_types=1);

namespace Tests\Feature\Procurement;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\{DB, Storage};
use Tests\TestCase;

final class AttachSupplierPaymentProofFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_attach_proof_to_pending_supplier_payment(): void
    {
        Storage::fake('local');

        $this->seedPayment('payment-1', 'invoice-1', 30000, '2026-03-20', 'pending', null);

        $response = $this
            ->from(route('admin.procurement.supplier-invoices.show', ['supplierInvoiceId' => 'invoice-1']))
            ->actingAs($this->user())
            ->post(route('admin.procurement.supplier-payments.proof.store', ['supplierPaymentId' => 'payment-1']), [
                'proof_file' => UploadedFile::fake()->create('proof.pdf', 120, 'application/pdf'),
            ]);

        $response->assertRedirect(route('admin.procurement.supplier-invoices.show', ['supplierInvoiceId' => 'invoice-1']));
        $response->assertSessionHas('success', 'Bukti pembayaran supplier berhasil diunggah.');

        $path = DB::table('supplier_payments')
            ->where('id', 'payment-1')
            ->value('proof_storage_path');

        $this->assertDatabaseHas('supplier_payments', [
            'id' => 'payment-1',
            'proof_status' => 'uploaded',
        ]);

        $this->assertIsString($path);
        $this->assertTrue(Storage::disk('local')->exists((string) $path));

        $context = (string) DB::table('audit_logs')
            ->where('event', 'supplier_payment_proof_attached')
            ->value('context');

        $this->assertStringContainsString('payment-1', $context);
    }

    private function user(): User
    {
        $user = User::query()->create([
            'name' => 'Admin',
            'email' => 'admin-proof@example.test',
            'password' => 'password123',
        ]);

        DB::table('actor_accesses')->insert([
            'actor_id' => (string) $user->getAuthIdentifier(),
            'role' => 'admin',
        ]);

        return $user;
    }

    private function seedPayment(
        string $id,
        string $invoiceId,
        int $amount,
        string $paidAt,
        string $proofStatus,
        ?string $proofPath
    ): void {
        DB::table('supplier_payments')->insert([
            'id' => $id,
            'supplier_invoice_id' => $invoiceId,
            'amount_rupiah' => $amount,
            'paid_at' => $paidAt,
            'proof_status' => $proofStatus,
            'proof_storage_path' => $proofPath,
        ]);
    }
}
