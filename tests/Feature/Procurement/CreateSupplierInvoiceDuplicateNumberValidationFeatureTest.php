<?php

declare(strict_types=1);

namespace Tests\Feature\Procurement;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Support\SeedsMinimalProcurementFixture;
use Tests\TestCase;

final class CreateSupplierInvoiceDuplicateNumberValidationFeatureTest extends TestCase
{
    use RefreshDatabase;
    use SeedsMinimalProcurementFixture;

    public function test_create_supplier_invoice_endpoint_rejects_duplicate_nomor_faktur_when_active_invoice_exists(): void
    {
        $this->loginAsKasir();
        $this->seedMinimalProduct('product-1', 'KB-001', 'Supra', 'Federal', 100, 15000);
        $this->seedMinimalSupplier('supplier-1', 'PT Sumber Makmur', 'pt sumber makmur');

        $this->seedExistingInvoice(
            invoiceId: 'invoice-1',
            supplierId: 'supplier-1',
            nomorFaktur: 'INV-SUP-001',
            voidedAt: null,
        );

        $response = $this->postJson('/procurement/supplier-invoices/create', [
            'nomor_faktur' => ' inv-sup-001 ',
            'nama_pt_pengirim' => 'PT Sumber Makmur',
            'tanggal_pengiriman' => '2026-03-12',
            'lines' => [
                [
                    'line_no' => 1,
                    'product_id' => 'product-1',
                    'qty_pcs' => 2,
                    'line_total_rupiah' => 20000,
                ],
            ],
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['nomor_faktur']);

        self::assertSame(
            'Nomor faktur sudah dipakai oleh nota supplier aktif.',
            data_get($response->json(), 'errors.nomor_faktur.0')
        );

        self::assertSame(
            1,
            DB::table('supplier_invoices')
                ->where('nomor_faktur_normalized', 'inv-sup-001')
                ->count()
        );
    }

    public function test_create_supplier_invoice_endpoint_allows_reusing_nomor_faktur_when_existing_invoice_is_voided(): void
    {
        $this->loginAsKasir();
        $this->seedMinimalProduct('product-1', 'KB-001', 'Supra', 'Federal', 100, 15000);
        $this->seedMinimalSupplier('supplier-1', 'PT Sumber Makmur', 'pt sumber makmur');

        $this->seedExistingInvoice(
            invoiceId: 'invoice-1',
            supplierId: 'supplier-1',
            nomorFaktur: 'INV-SUP-001',
            voidedAt: '2026-03-15 10:00:00',
        );

        $response = $this->postJson('/procurement/supplier-invoices/create', [
            'nomor_faktur' => 'INV-SUP-001',
            'nama_pt_pengirim' => 'PT Sumber Makmur',
            'tanggal_pengiriman' => '2026-03-12',
            'lines' => [
                [
                    'line_no' => 1,
                    'product_id' => 'product-1',
                    'qty_pcs' => 2,
                    'line_total_rupiah' => 20000,
                ],
            ],
        ]);

        $response->assertOk();

        self::assertSame(
            2,
            DB::table('supplier_invoices')
                ->where('nomor_faktur_normalized', 'inv-sup-001')
                ->count()
        );

        self::assertSame(
            1,
            DB::table('supplier_invoices')
                ->where('nomor_faktur_normalized', 'inv-sup-001')
                ->whereNull('voided_at')
                ->count()
        );

        self::assertSame(
            1,
            DB::table('supplier_invoices')
                ->where('nomor_faktur_normalized', 'inv-sup-001')
                ->whereNotNull('voided_at')
                ->count()
        );
    }

    private function seedExistingInvoice(
        string $invoiceId,
        string $supplierId,
        string $nomorFaktur,
        ?string $voidedAt,
    ): void {
        DB::table('supplier_invoices')->insert([
            'id' => $invoiceId,
            'supplier_id' => $supplierId,
            'supplier_nama_pt_pengirim_snapshot' => 'PT Sumber Makmur',
            'nomor_faktur' => $nomorFaktur,
            'nomor_faktur_normalized' => mb_strtolower(trim($nomorFaktur), 'UTF-8'),
            'document_kind' => 'invoice',
            'lifecycle_status' => 'active',
            'origin_supplier_invoice_id' => null,
            'superseded_by_supplier_invoice_id' => null,
            'tanggal_pengiriman' => '2026-03-10',
            'jatuh_tempo' => '2026-04-09',
            'grand_total_rupiah' => 20000,
            'voided_at' => $voidedAt,
            'void_reason' => $voidedAt !== null ? 'Seeded as voided' : null,
            'last_revision_no' => 1,
        ]);
    }
}
