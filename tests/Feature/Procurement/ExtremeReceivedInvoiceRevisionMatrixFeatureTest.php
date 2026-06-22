<?php

declare(strict_types=1);

namespace Tests\Feature\Procurement;

use App\Adapters\Out\Persistence\Eloquent\IdentityAccess\EloquentUser as User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Support\SeedsReceivedSupplierInvoiceRevisionMatrixFixture;
use Tests\TestCase;

final class ExtremeReceivedInvoiceRevisionMatrixFeatureTest extends TestCase
{
    use RefreshDatabase, SeedsReceivedSupplierInvoiceRevisionMatrixFixture;

    public function test_admin_can_revise_received_invoice_by_increasing_quantity(): void
    {
        $this->assertQtyDeltaRevision(3, 30000, 'stock_in', 1, 10000, 3, 30000);
    }

    public function test_admin_can_revise_received_invoice_by_decreasing_quantity(): void
    {
        $this->assertQtyDeltaRevision(1, 10000, 'stock_out', -1, -10000, 1, 10000);
    }

    public function test_admin_can_revise_received_invoice_by_changing_product_and_increasing_total(): void
    {
        $this->seedReceivedInvoiceBase();
        $this->seedReplacementProduct();

        $r = $this->actingAs($this->admin())->put(
            route('admin.procurement.supplier-invoices.update', ['supplierInvoiceId' => 'invoice-1']),
            $this->payload(['lines' => [[
                'previous_line_id' => 'invoice-line-1',
                'line_no' => 1,
                'product_id' => 'product-2',
                'qty_pcs' => 2,
                'line_total_rupiah' => 24000,
            ]]])
        );

        $r->assertRedirect(route('admin.procurement.supplier-invoices.show', ['supplierInvoiceId' => 'invoice-1']))
            ->assertSessionHas('success', 'Nota supplier berhasil diperbarui.');

        $this->assertDatabaseHas('supplier_invoices', ['id' => 'invoice-1', 'last_revision_no' => 2, 'grand_total_rupiah' => 24000]);
        $this->assertDatabaseHas('inventory_movements', ['product_id' => 'product-1', 'movement_type' => 'stock_out', 'source_type' => 'supplier_invoice_revision_delta_line', 'qty_delta' => -2, 'total_cost_rupiah' => -20000]);
        $this->assertDatabaseHas('inventory_movements', ['product_id' => 'product-2', 'movement_type' => 'stock_in', 'source_type' => 'supplier_invoice_revision_delta_line', 'qty_delta' => 2, 'total_cost_rupiah' => 24000]);
    }


    public function test_unpaid_received_invoice_cost_revision_creates_explicit_cost_revaluation_effect(): void
    {
        $this->seedReceivedInvoiceBase();

        $r = $this->actingAs($this->admin())->put(
            route('admin.procurement.supplier-invoices.update', ['supplierInvoiceId' => 'invoice-1']),
            $this->payload([
                'change_reason' => 'Koreksi landed cost diterima supplier.',
                'lines' => [[
                    'previous_line_id' => 'invoice-line-1',
                    'line_no' => 1,
                    'product_id' => 'product-1',
                    'qty_pcs' => 2,
                    'line_total_rupiah' => 22000,
                ]],
            ])
        );

        $r->assertRedirect(route('admin.procurement.supplier-invoices.show', ['supplierInvoiceId' => 'invoice-1']))
            ->assertSessionHas('success', 'Nota supplier berhasil diperbarui.');

        $this->assertDatabaseHas('supplier_invoices', [
            'id' => 'invoice-1',
            'last_revision_no' => 2,
            'grand_total_rupiah' => 22000,
        ]);

        $this->assertDatabaseHas('inventory_movements', [
            'product_id' => 'product-1',
            'movement_type' => 'cost_revaluation',
            'source_type' => 'supplier_invoice_cost_revaluation',
            'tanggal_mutasi' => '2026-03-17',
            'qty_delta' => 0,
            'unit_cost_rupiah' => 0,
            'total_cost_rupiah' => 2000,
        ]);

        $this->assertDatabaseHas('product_inventory', [
            'product_id' => 'product-1',
            'qty_on_hand' => 2,
        ]);

        $this->assertDatabaseHas('product_inventory_costing', [
            'product_id' => 'product-1',
            'avg_cost_rupiah' => 11000,
            'inventory_value_rupiah' => 22000,
        ]);
    }

    public function test_paid_received_invoice_cost_revision_can_increase_total_and_outstanding_remaining(): void
    {
        $this->seedReceivedInvoiceBase();
        $this->seedPayment(5000);

        $r = $this->actingAs($this->admin())->put(
            route('admin.procurement.supplier-invoices.update', ['supplierInvoiceId' => 'invoice-1']),
            $this->payload([
                'change_reason' => 'Koreksi modal setelah sebagian pembayaran tercatat.',
                'lines' => [[
                    'previous_line_id' => 'invoice-line-1',
                    'line_no' => 1,
                    'product_id' => 'product-1',
                    'qty_pcs' => 2,
                    'line_total_rupiah' => 22000,
                ]],
            ])
        );

        $r->assertRedirect(route('admin.procurement.supplier-invoices.show', ['supplierInvoiceId' => 'invoice-1']))
            ->assertSessionHas('success', 'Nota supplier berhasil diperbarui.');

        $grandTotal = (int) DB::table('supplier_invoices')
            ->where('id', 'invoice-1')
            ->value('grand_total_rupiah');

        $totalPaid = (int) DB::table('supplier_payments')
            ->where('supplier_invoice_id', 'invoice-1')
            ->sum('amount_rupiah');

        $this->assertSame(22000, $grandTotal);
        $this->assertSame(5000, $totalPaid);
        $this->assertSame(17000, $grandTotal - $totalPaid);

        $this->assertDatabaseHas('inventory_movements', [
            'product_id' => 'product-1',
            'movement_type' => 'cost_revaluation',
            'source_type' => 'supplier_invoice_cost_revaluation',
            'qty_delta' => 0,
            'unit_cost_rupiah' => 0,
            'total_cost_rupiah' => 2000,
        ]);
    }

    public function test_received_invoice_cost_revaluation_revision_writes_version_and_audit_snapshots(): void
    {
        $this->seedReceivedInvoiceBase();

        $r = $this->actingAs($this->admin())->put(
            route('admin.procurement.supplier-invoices.update', ['supplierInvoiceId' => 'invoice-1']),
            $this->payload([
                'change_reason' => 'Audit koreksi modal setelah receipt.',
                'lines' => [[
                    'previous_line_id' => 'invoice-line-1',
                    'line_no' => 1,
                    'product_id' => 'product-1',
                    'qty_pcs' => 2,
                    'line_total_rupiah' => 22000,
                ]],
            ])
        );

        $r->assertRedirect(route('admin.procurement.supplier-invoices.show', ['supplierInvoiceId' => 'invoice-1']))
            ->assertSessionHas('success', 'Nota supplier berhasil diperbarui.');

        $this->assertDatabaseHas('supplier_invoice_versions', [
            'supplier_invoice_id' => 'invoice-1',
            'revision_no' => 2,
            'event_name' => 'supplier_invoice_updated',
        ]);

        $auditEventId = (string) DB::table('audit_events')
            ->where('aggregate_type', 'supplier_invoice')
            ->where('aggregate_id', 'invoice-1')
            ->where('event_name', 'supplier_invoice_updated')
            ->value('id');

        $this->assertNotSame('', $auditEventId);

        $this->assertDatabaseHas('audit_event_snapshots', [
            'audit_event_id' => $auditEventId,
            'snapshot_kind' => 'before',
        ]);

        $this->assertDatabaseHas('audit_event_snapshots', [
            'audit_event_id' => $auditEventId,
            'snapshot_kind' => 'after',
        ]);

        $beforePayload = json_decode((string) DB::table('audit_event_snapshots')
            ->where('audit_event_id', $auditEventId)
            ->where('snapshot_kind', 'before')
            ->value('payload_json'), true);

        $afterPayload = json_decode((string) DB::table('audit_event_snapshots')
            ->where('audit_event_id', $auditEventId)
            ->where('snapshot_kind', 'after')
            ->value('payload_json'), true);

        $this->assertSame(20000, $beforePayload['grand_total_rupiah']);
        $this->assertSame(22000, $afterPayload['grand_total_rupiah']);
        $this->assertSame(10000, $beforePayload['lines'][0]['unit_cost_rupiah']);
        $this->assertSame(11000, $afterPayload['lines'][0]['unit_cost_rupiah']);
    }

    public function test_admin_cannot_revise_received_invoice_when_total_would_drop_below_paid(): void
    {
        $this->seedReceivedInvoiceBase();
        $this->seedPayment();

        $r = $this->actingAs($this->admin())
            ->from(route('admin.procurement.supplier-invoices.revise', ['supplierInvoiceId' => 'invoice-1']))
            ->put(route('admin.procurement.supplier-invoices.update', ['supplierInvoiceId' => 'invoice-1']), $this->payload([
                'change_reason' => 'Turun di bawah paid',
                'lines' => [[
                    'previous_line_id' => 'invoice-line-1',
                    'line_no' => 1,
                    'product_id' => 'product-1',
                    'qty_pcs' => 1,
                    'line_total_rupiah' => 4000,
                ]],
            ]));

        $r->assertRedirect(route('admin.procurement.supplier-invoices.revise', ['supplierInvoiceId' => 'invoice-1']))
            ->assertSessionHasErrors(['supplier_invoice']);

        $this->assertDatabaseHas('supplier_invoices', ['id' => 'invoice-1', 'last_revision_no' => 1, 'grand_total_rupiah' => 20000]);
    }

    public function test_admin_cannot_revise_received_invoice_when_delta_would_make_stock_negative(): void
    {
        $this->seedReceivedInvoiceBase();
        $this->seedReplacementProduct();
        $this->setProduct1Projection(1, 10000);

        $r = $this->actingAs($this->admin())
            ->from(route('admin.procurement.supplier-invoices.revise', ['supplierInvoiceId' => 'invoice-1']))
            ->put(route('admin.procurement.supplier-invoices.update', ['supplierInvoiceId' => 'invoice-1']), $this->payload([
                'lines' => [[
                    'previous_line_id' => 'invoice-line-1',
                    'line_no' => 1,
                    'product_id' => 'product-2',
                    'qty_pcs' => 2,
                    'line_total_rupiah' => 24000,
                ]],
            ]));

        $r->assertRedirect(route('admin.procurement.supplier-invoices.revise', ['supplierInvoiceId' => 'invoice-1']))
            ->assertSessionHasErrors(['supplier_invoice']);

        $this->assertDatabaseHas('product_inventory', ['product_id' => 'product-1', 'qty_on_hand' => 1]);
    }

    public function test_admin_cannot_revise_received_invoice_without_reason(): void
    {
        $this->seedReceivedInvoiceBase();

        $r = $this->actingAs($this->admin())
            ->from(route('admin.procurement.supplier-invoices.revise', ['supplierInvoiceId' => 'invoice-1']))
            ->put(route('admin.procurement.supplier-invoices.update', ['supplierInvoiceId' => 'invoice-1']), $this->payload(['change_reason' => '']));

        $r->assertRedirect(route('admin.procurement.supplier-invoices.revise', ['supplierInvoiceId' => 'invoice-1']))
            ->assertSessionHasErrors(['change_reason']);
    }

    public function test_admin_cannot_revise_received_invoice_with_invalid_shipment_date(): void
    {
        $this->seedReceivedInvoiceBase();

        $r = $this->actingAs($this->admin())
            ->from(route('admin.procurement.supplier-invoices.revise', ['supplierInvoiceId' => 'invoice-1']))
            ->put(route('admin.procurement.supplier-invoices.update', ['supplierInvoiceId' => 'invoice-1']), $this->payload([
                'tanggal_pengiriman' => '2026-15-99',
            ]));

        $r->assertRedirect(route('admin.procurement.supplier-invoices.revise', ['supplierInvoiceId' => 'invoice-1']))
            ->assertSessionHasErrors(['tanggal_pengiriman']);
    }

    private function assertQtyDeltaRevision(
        int $qty,
        int $total,
        string $movementType,
        int $deltaQty,
        int $deltaCost,
        int $projectedQty,
        int $projectedValue,
    ): void {
        $this->seedReceivedInvoiceBase();

        $r = $this->actingAs($this->admin())->put(
            route('admin.procurement.supplier-invoices.update', ['supplierInvoiceId' => 'invoice-1']),
            $this->payload(['lines' => [[
                'previous_line_id' => 'invoice-line-1',
                'line_no' => 1,
                'product_id' => 'product-1',
                'qty_pcs' => $qty,
                'line_total_rupiah' => $total,
            ]]])
        );

        $r->assertRedirect(route('admin.procurement.supplier-invoices.show', ['supplierInvoiceId' => 'invoice-1']))
            ->assertSessionHas('success', 'Nota supplier berhasil diperbarui.');

        $this->assertDatabaseHas('supplier_invoices', ['id' => 'invoice-1', 'last_revision_no' => 2, 'grand_total_rupiah' => $total]);
        $this->assertDatabaseHas('inventory_movements', [
            'product_id' => 'product-1',
            'movement_type' => $movementType,
            'source_type' => 'supplier_invoice_revision_delta_line',
            'qty_delta' => $deltaQty,
            'unit_cost_rupiah' => 10000,
            'total_cost_rupiah' => $deltaCost,
        ]);
        $this->assertDatabaseHas('product_inventory', ['product_id' => 'product-1', 'qty_on_hand' => $projectedQty]);
        $this->assertDatabaseHas('product_inventory_costing', [
            'product_id' => 'product-1',
            'avg_cost_rupiah' => 10000,
            'inventory_value_rupiah' => $projectedValue,
        ]);
    }

    private function payload(array $overrides = []): array
    {
        return array_replace_recursive([
            'expected_revision_no' => 1,
            'change_reason' => 'Koreksi matrix ekstrem received invoice.',
            'nomor_faktur' => 'INV-SUP-001',
            'nama_pt_pengirim' => 'PT Sumber Makmur',
            'tanggal_pengiriman' => '2026-03-15',
            'lines' => [[
                'previous_line_id' => 'invoice-line-1',
                'line_no' => 1,
                'product_id' => 'product-1',
                'qty_pcs' => 2,
                'line_total_rupiah' => 20000,
            ]],
        ], $overrides);
    }

    private function admin(): User
    {
        $u = User::query()->create([
            'name' => 'Admin Matrix',
            'email' => 'admin-matrix-received@example.test',
            'password' => 'password123',
        ]);

        DB::table('actor_accesses')->insert([
            'actor_id' => (string) $u->getAuthIdentifier(),
            'role' => 'admin',
        ]);

        return $u;
    }
}
