<?php

declare(strict_types=1);

namespace Tests\Feature\Procurement;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Support\SeedsMinimalProcurementFixture;
use Tests\TestCase;

final class ReceiveSupplierInvoiceFeatureTest extends TestCase
{
    use RefreshDatabase;
    use SeedsMinimalProcurementFixture;

    public function test_receive_supplier_invoice_endpoint_stores_receipt_movements_and_updates_inventory_projection(): void
    {
        $this->loginAsKasir();
        $this->seedMinimalProduct('product-1', 'KB-001', 'Supra', 'Federal', 100, 15000);
        $this->seedMinimalSupplier('supplier-1', 'PT Sumber Makmur', 'pt sumber makmur');
        $this->seedMinimalSupplierInvoice('invoice-1', 'supplier-1', '2026-03-12', '2026-04-12', 100000);
        $this->seedMinimalSupplierInvoiceLine('invoice-line-1', 'invoice-1', 'product-1', 10, 100000, 10000);

        DB::table('product_inventory')->insert([
            'product_id' => 'product-1',
            'qty_on_hand' => 3,
        ]);

        DB::table('product_inventory_costing')->insert([
            'product_id' => 'product-1',
            'avg_cost_rupiah' => 10000,
            'inventory_value_rupiah' => 30000,
        ]);

        $response = $this->postJson('/procurement/supplier-invoices/invoice-1/receive', [
            'tanggal_terima' => '2026-03-13',
            'lines' => [
                [
                    'supplier_invoice_line_id' => 'invoice-line-1',
                    'qty_diterima' => 4,
                ],
            ],
        ]);

        $response->assertOk();

        $this->assertDatabaseHas('supplier_receipts', [
            'supplier_invoice_id' => 'invoice-1',
            'tanggal_terima' => '2026-03-13',
        ]);

        $receipt = DB::table('supplier_receipts')
            ->where('supplier_invoice_id', 'invoice-1')
            ->first();

        $this->assertNotNull($receipt);

        $this->assertDatabaseHas('supplier_receipt_lines', [
            'supplier_receipt_id' => (string) $receipt->id,
            'supplier_invoice_line_id' => 'invoice-line-1',
            'qty_diterima' => 4,
        ]);

        $receiptLine = DB::table('supplier_receipt_lines')
            ->where('supplier_receipt_id', (string) $receipt->id)
            ->where('supplier_invoice_line_id', 'invoice-line-1')
            ->first();

        $this->assertNotNull($receiptLine);

        $this->assertDatabaseHas('inventory_movements', [
            'product_id' => 'product-1',
            'movement_type' => 'stock_in',
            'source_type' => 'supplier_receipt_line',
            'source_id' => (string) $receiptLine->id,
            'tanggal_mutasi' => '2026-03-13',
            'qty_delta' => 4,
            'unit_cost_rupiah' => 10000,
            'total_cost_rupiah' => 40000,
        ]);

        $this->assertDatabaseHas('product_inventory', [
            'product_id' => 'product-1',
            'qty_on_hand' => 7,
        ]);

        $this->assertDatabaseHas('product_inventory_costing', [
            'product_id' => 'product-1',
            'avg_cost_rupiah' => 10000,
            'inventory_value_rupiah' => 70000,
        ]);
    }

    public function test_receive_supplier_invoice_endpoint_rejects_unknown_supplier_invoice(): void
    {
        $this->loginAsKasir();

        $response = $this->postJson('/procurement/supplier-invoices/unknown-invoice/receive', [
            'tanggal_terima' => '2026-03-13',
            'lines' => [
                [
                    'supplier_invoice_line_id' => 'unknown-line',
                    'qty_diterima' => 1,
                ],
            ],
        ]);

        $response->assertStatus(422);

        $this->assertDatabaseCount('supplier_receipts', 0);
        $this->assertDatabaseCount('supplier_receipt_lines', 0);
        $this->assertDatabaseCount('inventory_movements', 0);
        $this->assertDatabaseCount('product_inventory', 0);
        $this->assertDatabaseCount('product_inventory_costing', 0);
    }

    public function test_receive_supplier_invoice_endpoint_rejects_line_that_does_not_belong_to_target_invoice(): void
    {
        $this->loginAsKasir();
        $this->seedMinimalProduct('product-1', 'KB-001', 'Supra', 'Federal', 100, 15000);
        $this->seedMinimalSupplier('supplier-1', 'PT Sumber Makmur', 'pt sumber makmur');

        $this->seedMinimalSupplierInvoice('invoice-1', 'supplier-1', '2026-03-12', '2026-04-12', 50000);
        $this->seedMinimalSupplierInvoice('invoice-2', 'supplier-1', '2026-03-12', '2026-04-12', 50000);

        $this->seedMinimalSupplierInvoiceLine('invoice-line-2', 'invoice-2', 'product-1', 5, 50000, 10000);

        $response = $this->postJson('/procurement/supplier-invoices/invoice-1/receive', [
            'tanggal_terima' => '2026-03-13',
            'lines' => [
                [
                    'supplier_invoice_line_id' => 'invoice-line-2',
                    'qty_diterima' => 2,
                ],
            ],
        ]);

        $response->assertStatus(422);

        $this->assertDatabaseCount('supplier_receipts', 0);
        $this->assertDatabaseCount('supplier_receipt_lines', 0);
        $this->assertDatabaseCount('inventory_movements', 0);
        $this->assertDatabaseCount('product_inventory', 0);
        $this->assertDatabaseCount('product_inventory_costing', 0);
    }

    public function test_receive_supplier_invoice_endpoint_rejects_over_receive_after_previous_receipts(): void
    {
        $this->loginAsKasir();
        $this->seedMinimalProduct('product-1', 'KB-001', 'Supra', 'Federal', 100, 15000);
        $this->seedMinimalSupplier('supplier-1', 'PT Sumber Makmur', 'pt sumber makmur');
        $this->seedMinimalSupplierInvoice('invoice-1', 'supplier-1', '2026-03-12', '2026-04-12', 100000);
        $this->seedMinimalSupplierInvoiceLine('invoice-line-1', 'invoice-1', 'product-1', 10, 100000, 10000);

        $this->seedMinimalSupplierReceipt('receipt-1', 'invoice-1', '2026-03-13');
        $this->seedMinimalSupplierReceiptLine('receipt-line-1', 'receipt-1', 'invoice-line-1', 7);

        $response = $this->postJson('/procurement/supplier-invoices/invoice-1/receive', [
            'tanggal_terima' => '2026-03-14',
            'lines' => [
                [
                    'supplier_invoice_line_id' => 'invoice-line-1',
                    'qty_diterima' => 4,
                ],
            ],
        ]);

        $response->assertStatus(422);

        $this->assertDatabaseCount('supplier_receipts', 1);
        $this->assertDatabaseCount('supplier_receipt_lines', 1);
        $this->assertDatabaseCount('inventory_movements', 0);
        $this->assertDatabaseCount('product_inventory', 0);
        $this->assertDatabaseCount('product_inventory_costing', 0);
    }
}
