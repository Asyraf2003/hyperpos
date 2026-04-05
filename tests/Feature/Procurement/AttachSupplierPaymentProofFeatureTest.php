<?php

declare(strict_types=1);

namespace Tests\Feature\Procurement;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

final class AttachSupplierPaymentProofFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_attach_multiple_proofs_to_pending_supplier_payment(): void
    {
        Storage::fake('local');

        $this->seedPayment('payment-1', 'invoice-1', 30000, '2026-03-20', 'pending', null);

        $response = $this
            ->from(route('admin.procurement.supplier-invoices.show', ['supplierInvoiceId' => 'invoice-1']))
            ->actingAs($this->user())
            ->post(route('admin.procurement.supplier-payments.proof.store', ['supplierPaymentId' => 'payment-1']), [
                'proof_files' => [
                    UploadedFile::fake()->create('proof-a.pdf', 120, 'application/pdf'),
                    UploadedFile::fake()->image('proof-b.jpg'),
                ],
            ]);

        $response->assertRedirect(route('admin.procurement.supplier-invoices.show', ['supplierInvoiceId' => 'invoice-1']));
        $response->assertSessionHas('success', 'Bukti pembayaran supplier berhasil diunggah.');

        $this->assertDatabaseHas('supplier_payments', [
            'id' => 'payment-1',
            'proof_status' => 'uploaded',
            'proof_storage_path' => null,
        ]);

        $attachments = DB::table('supplier_payment_proof_attachments')
            ->where('supplier_payment_id', 'payment-1')
            ->orderBy('uploaded_at')
            ->orderBy('id')
            ->get();

        $this->assertCount(2, $attachments);

        foreach ($attachments as $attachment) {
            $this->assertNotEmpty((string) $attachment->storage_path);
            $this->assertTrue(Storage::disk('local')->exists((string) $attachment->storage_path));
        }

        $context = (string) DB::table('audit_logs')
            ->where('event', 'supplier_payment_proof_attached')
            ->value('context');

        $this->assertStringContainsString('payment-1', $context);
        $this->assertStringContainsString('attachment_count', $context);
    }

    public function test_admin_can_append_more_proofs_to_same_supplier_payment_after_first_upload(): void
    {
        Storage::fake('local');

        $this->seedPayment('payment-1', 'invoice-1', 30000, '2026-03-20', 'uploaded', null);
        $this->seedAttachment(
            'attachment-1',
            'payment-1',
            'supplier-payment-proofs/payment-1/existing-proof.pdf',
            'existing-proof.pdf',
            'application/pdf',
            12345,
            '2026-03-20 10:00:00',
            'actor-1',
        );

        Storage::disk('local')->put('supplier-payment-proofs/payment-1/existing-proof.pdf', 'existing');

        $response = $this
            ->from(route('admin.procurement.supplier-invoices.show', ['supplierInvoiceId' => 'invoice-1']))
            ->actingAs($this->user())
            ->post(route('admin.procurement.supplier-payments.proof.store', ['supplierPaymentId' => 'payment-1']), [
                'proof_files' => [
                    UploadedFile::fake()->create('proof-c.pdf', 100, 'application/pdf'),
                    UploadedFile::fake()->image('proof-d.png'),
                ],
            ]);

        $response->assertRedirect(route('admin.procurement.supplier-invoices.show', ['supplierInvoiceId' => 'invoice-1']));
        $response->assertSessionHas('success', 'Bukti pembayaran supplier berhasil diunggah.');

        $this->assertDatabaseHas('supplier_payments', [
            'id' => 'payment-1',
            'proof_status' => 'uploaded',
            'proof_storage_path' => null,
        ]);

        $attachments = DB::table('supplier_payment_proof_attachments')
            ->where('supplier_payment_id', 'payment-1')
            ->orderBy('uploaded_at')
            ->orderBy('id')
            ->get();

        $this->assertCount(3, $attachments);

        foreach ($attachments as $attachment) {
            $this->assertNotEmpty((string) $attachment->storage_path);
            $this->assertTrue(Storage::disk('local')->exists((string) $attachment->storage_path));
        }
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

    private function seedAttachment(
        string $id,
        string $supplierPaymentId,
        string $storagePath,
        string $originalFilename,
        string $mimeType,
        int $fileSizeBytes,
        string $uploadedAt,
        string $uploadedByActorId
    ): void {
        DB::table('supplier_payment_proof_attachments')->insert([
            'id' => $id,
            'supplier_payment_id' => $supplierPaymentId,
            'storage_path' => $storagePath,
            'original_filename' => $originalFilename,
            'mime_type' => $mimeType,
            'file_size_bytes' => $fileSizeBytes,
            'uploaded_at' => $uploadedAt,
            'uploaded_by_actor_id' => $uploadedByActorId,
        ]);
    }
}
