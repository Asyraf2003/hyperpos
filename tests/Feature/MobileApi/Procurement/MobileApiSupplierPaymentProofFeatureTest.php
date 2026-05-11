<?php

declare(strict_types=1);

namespace Tests\Feature\MobileApi\Procurement;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\Support\SeedsMinimalProcurementFixture;
use Tests\TestCase;

final class MobileApiSupplierPaymentProofFeatureTest extends TestCase
{
    use RefreshDatabase;
    use SeedsMinimalProcurementFixture;

    public function test_supplier_payment_proof_upload_requires_mobile_api_token(): void
    {
        Storage::fake('local');

        $response = $this->post('/api/v1/supplier-payments/payment-1/proofs', [
            'proof_files' => [
                UploadedFile::fake()->create('proof-mobile.pdf', 120, 'application/pdf'),
            ],
        ], [
            'Accept' => 'application/json',
        ]);

        $response->assertStatus(401);
        $response->assertJson([
            'success' => false,
            'data' => null,
            'message' => 'Autentikasi diperlukan.',
            'errors' => [
                'token' => ['UNAUTHENTICATED'],
            ],
        ]);
    }

    public function test_cashier_mobile_token_cannot_upload_supplier_payment_proof(): void
    {
        Storage::fake('local');
        $this->seedPaymentFixture('payment-mobile-proof-1');

        $token = $this->loginMobileToken(
            email: 'mobile-kasir-payment-proof-upload@example.test',
            role: 'kasir',
        );

        $response = $this
            ->withHeader('Authorization', 'Bearer '.$token)
            ->post('/api/v1/supplier-payments/payment-mobile-proof-1/proofs', [
                'proof_files' => [
                    UploadedFile::fake()->create('proof-mobile.pdf', 120, 'application/pdf'),
                ],
            ], [
                'Accept' => 'application/json',
            ]);

        $response->assertStatus(403);
        $response->assertJson([
            'success' => false,
            'data' => null,
            'message' => 'Akses bukti pembayaran supplier mobile hanya untuk admin.',
            'errors' => [
                'role' => ['ADMIN_ONLY'],
            ],
        ]);
    }

    public function test_admin_can_upload_supplier_payment_proof(): void
    {
        Storage::fake('local');
        $this->seedPaymentFixture('payment-mobile-proof-1');

        $token = $this->loginMobileToken(
            email: 'mobile-admin-payment-proof-upload@example.test',
            role: 'admin',
        );

        $response = $this
            ->withHeader('Authorization', 'Bearer '.$token)
            ->post('/api/v1/supplier-payments/payment-mobile-proof-1/proofs', [
                'proof_files' => [
                    UploadedFile::fake()->create('proof-mobile.pdf', 120, 'application/pdf'),
                ],
            ], [
                'Accept' => 'application/json',
            ]);

        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'data' => [
                'supplier_payment_id' => 'payment-mobile-proof-1',
                'proof_status' => 'uploaded',
                'attachment_count' => 1,
            ],
            'message' => 'Bukti pembayaran supplier berhasil diunggah.',
            'errors' => null,
        ]);

        $this->assertDatabaseHas('supplier_payments', [
            'id' => 'payment-mobile-proof-1',
            'proof_status' => 'uploaded',
            'proof_storage_path' => null,
        ]);

        $attachments = DB::table('supplier_payment_proof_attachments')
            ->where('supplier_payment_id', 'payment-mobile-proof-1')
            ->get();

        self::assertCount(1, $attachments);

        $storedPath = (string) $attachments->first()->storage_path;
        self::assertNotSame('', $storedPath);
        self::assertTrue(Storage::disk('local')->exists($storedPath));
    }

    public function test_supplier_payment_proof_attachment_view_requires_mobile_api_token(): void
    {
        $response = $this->getJson('/api/v1/supplier-payment-proof-attachments/attachment-mobile-proof-1');

        $response->assertStatus(401);
        $response->assertJson([
            'success' => false,
            'data' => null,
            'message' => 'Autentikasi diperlukan.',
            'errors' => [
                'token' => ['UNAUTHENTICATED'],
            ],
        ]);
    }

    public function test_cashier_mobile_token_cannot_view_supplier_payment_proof_attachment(): void
    {
        Storage::fake('local');
        $this->storePdfFixture('supplier-payment-proofs/payment-mobile-proof-1/proof.pdf');
        $this->seedPaymentFixture('payment-mobile-proof-1');
        $this->seedAttachment(
            'attachment-mobile-proof-1',
            'payment-mobile-proof-1',
            'supplier-payment-proofs/payment-mobile-proof-1/proof.pdf',
            'proof.pdf',
            'application/pdf',
        );

        $token = $this->loginMobileToken(
            email: 'mobile-kasir-payment-proof-view@example.test',
            role: 'kasir',
        );

        $response = $this
            ->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/v1/supplier-payment-proof-attachments/attachment-mobile-proof-1');

        $response->assertStatus(403);
        $response->assertJson([
            'success' => false,
            'data' => null,
            'message' => 'Akses bukti pembayaran supplier mobile hanya untuk admin.',
            'errors' => [
                'role' => ['ADMIN_ONLY'],
            ],
        ]);
    }

    public function test_admin_can_view_supplier_payment_proof_attachment_with_safe_headers(): void
    {
        Storage::fake('local');
        $this->storePdfFixture('supplier-payment-proofs/payment-mobile-proof-1/proof.pdf');
        $this->seedPaymentFixture('payment-mobile-proof-1');
        $this->seedAttachment(
            'attachment-mobile-proof-1',
            'payment-mobile-proof-1',
            'supplier-payment-proofs/payment-mobile-proof-1/proof.pdf',
            'proof.pdf',
            'application/pdf',
        );

        $token = $this->loginMobileToken(
            email: 'mobile-admin-payment-proof-view@example.test',
            role: 'admin',
        );

        $response = $this
            ->withHeader('Authorization', 'Bearer '.$token)
            ->get('/api/v1/supplier-payment-proof-attachments/attachment-mobile-proof-1', [
                'Accept' => 'application/pdf',
            ]);

        $response->assertOk();
        self::assertStringContainsString('application/pdf', (string) $response->headers->get('content-type'));
        self::assertStringContainsString('inline', (string) $response->headers->get('content-disposition'));
        self::assertSame('nosniff', strtolower((string) $response->headers->get('x-content-type-options')));
    }

    private function loginMobileToken(string $email, string $role): string
    {
        $this->createUserWithRole($email, $role);

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => $email,
            'password' => 'password123',
            'device_name' => 'Redmi 12',
        ]);

        $response->assertOk();

        return (string) $response->json('data.token');
    }

    private function createUserWithRole(string $email, string $role): User
    {
        $user = User::query()->create([
            'name' => 'Mobile Supplier Payment Proof User',
            'email' => $email,
            'password' => 'password123',
        ]);

        DB::table('actor_accesses')->insert([
            'actor_id' => (string) $user->getAuthIdentifier(),
            'role' => $role,
        ]);

        return $user;
    }

    private function seedPaymentFixture(string $paymentId): void
    {
        $this->seedMinimalSupplier('supplier-mobile-proof-1', 'PT Supplier Proof', 'pt supplier proof');
        $this->seedMinimalProduct('product-mobile-proof-1', 'KB-PRF-001', 'Ban Proof', 'Federal', 100, 75000);

        $this->seedMinimalSupplierInvoice(
            'invoice-mobile-proof-1',
            'supplier-mobile-proof-1',
            '2026-05-10',
            '2026-05-20',
            100000,
            'PT Supplier Proof'
        );

        $this->seedMinimalSupplierInvoiceLine(
            'invoice-line-mobile-proof-1',
            'invoice-mobile-proof-1',
            'product-mobile-proof-1',
            2,
            100000,
            50000,
            'KB-PRF-001',
            'Ban Proof',
            'Federal',
            100
        );

        $this->seedMinimalSupplierPayment(
            $paymentId,
            'invoice-mobile-proof-1',
            30000,
            '2026-05-12',
            'pending'
        );
    }

    private function seedAttachment(
        string $id,
        string $supplierPaymentId,
        string $storagePath,
        string $originalFilename,
        string $mimeType
    ): void {
        DB::table('supplier_payment_proof_attachments')->insert([
            'id' => $id,
            'supplier_payment_id' => $supplierPaymentId,
            'storage_path' => $storagePath,
            'original_filename' => $originalFilename,
            'mime_type' => $mimeType,
            'file_size_bytes' => 12345,
            'uploaded_at' => '2026-05-12 10:00:00',
            'uploaded_by_actor_id' => 'actor-mobile-proof-1',
        ]);
    }

    private function storePdfFixture(string $path): void
    {
        Storage::disk('local')->put(
            $path,
            "%PDF-1.4\n1 0 obj\n<< /Type /Catalog >>\nendobj\ntrailer\n<< /Root 1 0 R >>\n%%EOF\n",
        );
    }
}
