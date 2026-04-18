<?php

declare(strict_types=1);

namespace Tests\Feature\Procurement;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\SeedsMinimalProcurementFixture;
use Tests\TestCase;

final class ExtremeReceiveRequestValidationMatrixFeatureTest extends TestCase
{
    use RefreshDatabase, SeedsMinimalProcurementFixture;

    public function test_receive_rejects_missing_tanggal_terima(): void
    {
        $this->seedBase();
        $this->postReceive(['tanggal_terima' => null])->assertStatus(422);
        $this->assertNoReceiptSideEffect();
    }

    public function test_receive_rejects_invalid_tanggal_terima_format(): void
    {
        $this->seedBase();
        $this->postReceive(['tanggal_terima' => '13-03-2026'])->assertStatus(422);
        $this->assertNoReceiptSideEffect();
    }

    public function test_receive_rejects_empty_lines(): void
    {
        $this->seedBase();
        $this->postReceive(['lines' => []])->assertStatus(422);
        $this->assertNoReceiptSideEffect();
    }

    public function test_receive_rejects_zero_qty_diterima(): void
    {
        $this->seedBase();
        $this->postReceive([
            'lines' => [[
                'supplier_invoice_line_id' => 'invoice-line-1',
                'qty_diterima' => 0,
            ]],
        ])->assertStatus(422);
        $this->assertNoReceiptSideEffect();
    }

    public function test_receive_rejects_negative_qty_diterima(): void
    {
        $this->seedBase();
        $this->postReceive([
            'lines' => [[
                'supplier_invoice_line_id' => 'invoice-line-1',
                'qty_diterima' => -1,
            ]],
        ])->assertStatus(422);
        $this->assertNoReceiptSideEffect();
    }

    public function test_receive_rejects_missing_supplier_invoice_line_id(): void
    {
        $this->seedBase();
        $this->postReceive([
            'lines' => [[
                'qty_diterima' => 1,
            ]],
        ])->assertStatus(422);
        $this->assertNoReceiptSideEffect();
    }

    private function seedBase(): void
    {
        $this->loginAsKasir();
        $this->seedMinimalProduct('product-1', 'KB-001', 'Supra', 'Federal', 100, 15000);
        $this->seedMinimalSupplier('supplier-1', 'PT Sumber Makmur', 'pt sumber makmur');
        $this->seedMinimalSupplierInvoice('invoice-1', 'supplier-1', '2026-03-12', '2026-04-12', 100000);
        $this->seedMinimalSupplierInvoiceLine('invoice-line-1', 'invoice-1', 'product-1', 10, 100000, 10000);
    }

    private function postReceive(array $overrides = [])
    {
        $payload = [
            'tanggal_terima' => '2026-03-13',
            'lines' => [[
                'supplier_invoice_line_id' => 'invoice-line-1',
                'qty_diterima' => 1,
            ]],
        ];

        if (array_key_exists('tanggal_terima', $overrides)) {
            $payload['tanggal_terima'] = $overrides['tanggal_terima'];
            unset($overrides['tanggal_terima']);
        }

        if (array_key_exists('lines', $overrides)) {
            $payload['lines'] = $overrides['lines'];
            unset($overrides['lines']);
        }

        $payload = array_replace($payload, $overrides);

        return $this->postJson('/procurement/supplier-invoices/invoice-1/receive', $payload);
    }

    private function assertNoReceiptSideEffect(): void
    {
        $this->assertDatabaseCount('supplier_receipts', 0);
        $this->assertDatabaseCount('supplier_receipt_lines', 0);
        $this->assertDatabaseCount('inventory_movements', 0);
        $this->assertDatabaseCount('product_inventory', 0);
        $this->assertDatabaseCount('product_inventory_costing', 0);
    }
}
