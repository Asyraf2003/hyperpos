<?php

declare(strict_types=1);

namespace Tests\Feature\Procurement;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

final class ServeSupplierPaymentProofAttachmentFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_preview_supplier_payment_proof_attachment_inline(): void
    {
        Storage::fake('local');
        Storage::disk('local')->put('supplier-payment-proofs/payment-1/proof.pdf', 'dummy-pdf');

        $this->seedPayment('payment-1');
        $this->seedAttachment(
            'attachment-1',
            'payment-1',
            'supplier-payment-proofs/payment-1/proof.pdf',
            'proof.pdf',
            'application/pdf',
        );

        $response = $this->actingAs($this->user('admin'))
            ->get(route('admin.procurement.supplier-payment-proof-attachments.show', [
                'attachmentId' => 'attachment-1',
            ]));

        $response->assertOk();
        self::assertStringContainsString('application/pdf', (string) $response->headers->get('content-type'));
        self::assertStringContainsString('inline', (string) $response->headers->get('content-disposition'));
    }

    public function test_admin_can_download_supplier_payment_proof_attachment(): void
    {
        Storage::fake('local');
        Storage::disk('local')->put('supplier-payment-proofs/payment-1/proof.jpg', 'dummy-image');

        $this->seedPayment('payment-1');
        $this->seedAttachment(
            'attachment-1',
            'payment-1',
            'supplier-payment-proofs/payment-1/proof.jpg',
            'proof.jpg',
            'image/jpeg',
        );

        $response = $this->actingAs($this->user('admin'))
            ->get(route('admin.procurement.supplier-payment-proof-attachments.show', [
                'attachmentId' => 'attachment-1',
                'download' => 1,
            ]));

        $response->assertOk();
        self::assertStringContainsString('image/jpeg', (string) $response->headers->get('content-type'));
        self::assertStringContainsString('attachment', (string) $response->headers->get('content-disposition'));
    }

    private function user(string $role): User
    {
        $user = User::query()->create([
            'name' => 'Test',
            'email' => $role . '-proof-file@example.test',
            'password' => 'password123',
        ]);

        DB::table('actor_accesses')->insert([
            'actor_id' => (string) $user->getAuthIdentifier(),
            'role' => $role,
        ]);

        return $user;
    }

    private function seedPayment(string $id): void
    {
        DB::table('supplier_payments')->insert([
            'id' => $id,
            'supplier_invoice_id' => 'invoice-1',
            'amount_rupiah' => 30000,
            'paid_at' => '2026-03-20',
            'proof_status' => 'uploaded',
            'proof_storage_path' => null,
        ]);
    }

    private function seedAttachment(
        string $id,
        string $supplierPaymentId,
        string $storagePath,
        string $originalFilename,
        string $mimeType,
    ): void {
        DB::table('supplier_payment_proof_attachments')->insert([
            'id' => $id,
            'supplier_payment_id' => $supplierPaymentId,
            'storage_path' => $storagePath,
            'original_filename' => $originalFilename,
            'mime_type' => $mimeType,
            'file_size_bytes' => 12345,
            'uploaded_at' => '2026-03-20 10:00:00',
            'uploaded_by_actor_id' => 'actor-1',
        ]);
    }
}
