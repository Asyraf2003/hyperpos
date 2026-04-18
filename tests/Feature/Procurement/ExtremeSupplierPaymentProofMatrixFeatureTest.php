<?php

declare(strict_types=1);

namespace Tests\Feature\Procurement;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\Support\SeedsSupplierPaymentProofMatrixFixture;
use Tests\TestCase;

final class ExtremeSupplierPaymentProofMatrixFeatureTest extends TestCase
{
    use RefreshDatabase, SeedsSupplierPaymentProofMatrixFixture;

    public function test_admin_can_attach_exactly_three_valid_proof_files(): void
    {
        $this->fakeStorage();
        $this->seedPaymentFixture();

        $response = $this->actingAs($this->admin())
            ->from(route('admin.procurement.supplier-invoices.show', ['supplierInvoiceId' => 'invoice-1']))
            ->post(route('admin.procurement.supplier-payments.proof.store', ['supplierPaymentId' => 'payment-1']), [
                'proof_files' => $this->threeValidFiles(),
            ]);

        $response->assertRedirect(route('admin.procurement.supplier-invoices.show', ['supplierInvoiceId' => 'invoice-1']))
            ->assertSessionHas('success', 'Bukti pembayaran supplier berhasil diunggah.');

        $this->assertDatabaseHas('supplier_payments', [
            'id' => 'payment-1',
            'proof_status' => 'uploaded',
        ]);

        $this->assertSame(3, DB::table('supplier_payment_proof_attachments')->where('supplier_payment_id', 'payment-1')->count());
    }

    public function test_admin_cannot_attach_more_than_three_proof_files(): void
    {
        $this->fakeStorage();
        $this->seedPaymentFixture();

        $response = $this->actingAs($this->admin())
            ->from(route('admin.procurement.supplier-invoices.show', ['supplierInvoiceId' => 'invoice-1']))
            ->post(route('admin.procurement.supplier-payments.proof.store', ['supplierPaymentId' => 'payment-1']), [
                'proof_files' => $this->fourValidFiles(),
            ]);

        $response->assertRedirect(route('admin.procurement.supplier-invoices.show', ['supplierInvoiceId' => 'invoice-1']))
            ->assertSessionHasErrors(['proof_files']);

        $this->assertSame(0, DB::table('supplier_payment_proof_attachments')->where('supplier_payment_id', 'payment-1')->count());
    }

    public function test_admin_cannot_attach_invalid_mime_proof_file(): void
    {
        $this->fakeStorage();
        $this->seedPaymentFixture();

        $response = $this->actingAs($this->admin())
            ->from(route('admin.procurement.supplier-invoices.show', ['supplierInvoiceId' => 'invoice-1']))
            ->post(route('admin.procurement.supplier-payments.proof.store', ['supplierPaymentId' => 'payment-1']), [
                'proof_files' => [
                    UploadedFile::fake()->create('proof.txt', 10, 'text/plain'),
                ],
            ]);

        $response->assertRedirect(route('admin.procurement.supplier-invoices.show', ['supplierInvoiceId' => 'invoice-1']))
            ->assertSessionHasErrors(['proof_files.0']);

        $this->assertSame(0, DB::table('supplier_payment_proof_attachments')->where('supplier_payment_id', 'payment-1')->count());
    }

    public function test_admin_cannot_attach_oversized_proof_file(): void
    {
        $this->fakeStorage();
        $this->seedPaymentFixture();

        $response = $this->actingAs($this->admin())
            ->from(route('admin.procurement.supplier-invoices.show', ['supplierInvoiceId' => 'invoice-1']))
            ->post(route('admin.procurement.supplier-payments.proof.store', ['supplierPaymentId' => 'payment-1']), [
                'proof_files' => [
                    UploadedFile::fake()->create('huge.pdf', 3000, 'application/pdf'),
                ],
            ]);

        $response->assertRedirect(route('admin.procurement.supplier-invoices.show', ['supplierInvoiceId' => 'invoice-1']))
            ->assertSessionHasErrors(['proof_files.0']);

        $this->assertSame(0, DB::table('supplier_payment_proof_attachments')->where('supplier_payment_id', 'payment-1')->count());
    }

    public function test_guest_is_redirected_to_login_when_attaching_supplier_payment_proof(): void
    {
        $this->fakeStorage();
        $this->seedPaymentFixture();

        $this->post(route('admin.procurement.supplier-payments.proof.store', ['supplierPaymentId' => 'payment-1']), [
            'proof_files' => $this->threeValidFiles(),
        ])->assertRedirect(route('login'));
    }

    public function test_admin_can_preview_inline_and_download_existing_attachment(): void
    {
        Storage::fake('local');
        Storage::disk('local')->put('supplier-payment-proofs/payment-1/proof.pdf', 'dummy-pdf');
        Storage::disk('local')->put('supplier-payment-proofs/payment-1/proof.jpg', 'dummy-image');

        $this->seedPaymentFixture('payment-1', 'invoice-1', 'uploaded');
        $this->seedAttachment('attachment-1', 'payment-1', 'supplier-payment-proofs/payment-1/proof.pdf', 'proof.pdf', 'application/pdf');
        $this->seedAttachment('attachment-2', 'payment-1', 'supplier-payment-proofs/payment-1/proof.jpg', 'proof.jpg', 'image/jpeg');

        $admin = $this->admin();

        $inline = $this->actingAs($admin)
            ->get(route('admin.procurement.supplier-payment-proof-attachments.show', ['attachmentId' => 'attachment-1']));

        $inline->assertOk();
        self::assertStringContainsString('application/pdf', (string) $inline->headers->get('content-type'));
        self::assertStringContainsString('inline', (string) $inline->headers->get('content-disposition'));

        $download = $this->actingAs($admin)
            ->get(route('admin.procurement.supplier-payment-proof-attachments.show', [
                'attachmentId' => 'attachment-2',
                'download' => 1,
            ]));

        $download->assertOk();
        self::assertStringContainsString('image/jpeg', (string) $download->headers->get('content-type'));
        self::assertStringContainsString('attachment', (string) $download->headers->get('content-disposition'));
    }
}
