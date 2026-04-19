<?php

declare(strict_types=1);

namespace Tests\Feature\Procurement;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\SeedsMinimalProcurementFixture;
use Tests\TestCase;

final class CreateSupplierInvoiceGrandTotalGuardFeatureTest extends TestCase
{
    use RefreshDatabase;
    use SeedsMinimalProcurementFixture;

    public function test_create_supplier_invoice_rejects_overflowing_grand_total_even_when_auto_receive_is_disabled(): void
    {
        $this->loginAsKasir();
        $this->seedMinimalProduct('product-1', 'KB-001', 'Supra', 'Federal', 100, 15000);
        $this->seedMinimalProduct('product-2', 'KB-002', 'Vario', 'Federal', 90, 17000);

        $response = $this->postJson('/procurement/supplier-invoices/create', [
            'nomor_faktur' => 'INV-SUP-OVERFLOW-001',
            'nama_pt_pengirim' => 'PT Sumber Makmur',
            'tanggal_pengiriman' => '2026-03-12',
            'auto_receive' => false,
            'lines' => [
                [
                    'line_no' => 1,
                    'product_id' => 'product-1',
                    'qty_pcs' => 1,
                    'line_total_rupiah' => 2147483647,
                ],
                [
                    'line_no' => 2,
                    'product_id' => 'product-2',
                    'qty_pcs' => 1,
                    'line_total_rupiah' => 1,
                ],
            ],
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['supplier_invoice']);

        $this->assertDatabaseCount('suppliers', 0);
        $this->assertDatabaseCount('supplier_invoices', 0);
        $this->assertDatabaseCount('supplier_invoice_lines', 0);
        $this->assertDatabaseCount('supplier_invoice_versions', 0);
        $this->assertDatabaseCount('audit_events', 0);
        $this->assertDatabaseCount('audit_event_snapshots', 0);
        $this->assertDatabaseCount('supplier_receipts', 0);
        $this->assertDatabaseCount('supplier_receipt_lines', 0);
        $this->assertDatabaseCount('inventory_movements', 0);
    }
}
