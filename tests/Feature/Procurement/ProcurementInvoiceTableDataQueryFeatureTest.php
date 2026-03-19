<?php

declare(strict_types=1);

namespace Tests\Feature\Procurement;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

final class ProcurementInvoiceTableDataQueryFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_search_procurement_invoice_table_by_supplier_name(): void
    {
        $this->seedSupplier('supplier-1', 'PT Federal Abadi');
        $this->seedSupplier('supplier-2', 'PT Astra Otoparts');

        $this->seedInvoice('invoice-1', 'supplier-1', '2026-03-15', '2026-04-15', 100000);
        $this->seedInvoice('invoice-2', 'supplier-2', '2026-03-16', '2026-04-16', 50000);

        $response = $this->actingAs($this->admin())
            ->get(route('admin.procurement.supplier-invoices.table', ['q' => 'Federal']));

        $response->assertOk();
        $response->assertJsonCount(1, 'data.rows');
        $response->assertJsonPath('data.rows.0.supplier_invoice_id', 'invoice-1');
        $response->assertJsonPath('data.meta.filters.q', 'Federal');
    }

    public function test_admin_can_sort_procurement_invoice_table_by_outstanding_desc(): void
    {
        $this->seedSupplier('supplier-1', 'PT Alpha Motor');
        $this->seedSupplier('supplier-2', 'PT Zebra Parts');

        $this->seedInvoice('invoice-1', 'supplier-1', '2026-03-15', '2026-04-15', 100000);
        $this->seedInvoice('invoice-2', 'supplier-2', '2026-03-16', '2026-04-16', 100000);

        $this->seedPayment('payment-1', 'invoice-1', 70000, '2026-03-16', 'pending');
        $this->seedPayment('payment-2', 'invoice-2', 10000, '2026-03-17', 'pending');

        $response = $this->actingAs($this->admin())
            ->get(route('admin.procurement.supplier-invoices.table', [
                'sort_by' => 'outstanding_rupiah',
                'sort_dir' => 'desc',
            ]));

        $response->assertOk();
        $response->assertJsonPath('data.rows.0.supplier_invoice_id', 'invoice-2');
        $response->assertJsonPath('data.rows.1.supplier_invoice_id', 'invoice-1');
    }

    public function test_admin_can_filter_procurement_invoice_table_by_shipment_date_range(): void
    {
        $this->seedSupplier('supplier-1', 'PT Federal Abadi');

        $this->seedInvoice('invoice-1', 'supplier-1', '2026-03-10', '2026-04-10', 100000);
        $this->seedInvoice('invoice-2', 'supplier-1', '2026-03-15', '2026-04-15', 110000);
        $this->seedInvoice('invoice-3', 'supplier-1', '2026-03-20', '2026-04-20', 120000);

        $response = $this->actingAs($this->admin())
            ->get(route('admin.procurement.supplier-invoices.table', [
                'shipment_date_from' => '2026-03-12',
                'shipment_date_to' => '2026-03-18',
                'sort_by' => 'shipment_date',
                'sort_dir' => 'asc',
            ]));

        $response->assertOk();
        $response->assertJsonCount(1, 'data.rows');
        $response->assertJsonPath('data.rows.0.supplier_invoice_id', 'invoice-2');
        $response->assertJsonPath('data.meta.filters.shipment_date_from', '2026-03-12');
        $response->assertJsonPath('data.meta.filters.shipment_date_to', '2026-03-18');
    }

    public function test_admin_can_access_second_page_of_procurement_invoice_table(): void
    {
        $this->seedSupplier('supplier-1', 'PT Federal Abadi');

        for ($i = 1; $i <= 11; $i++) {
            $this->seedInvoice(
                'invoice-' . $i,
                'supplier-1',
                '2026-03-' . str_pad((string) $i, 2, '0', STR_PAD_LEFT),
                '2026-04-' . str_pad((string) $i, 2, '0', STR_PAD_LEFT),
                10000 + $i
            );
        }

        $response = $this->actingAs($this->admin())
            ->get(route('admin.procurement.supplier-invoices.table', [
                'page' => 2,
                'sort_by' => 'shipment_date',
                'sort_dir' => 'asc',
            ]));

        $response->assertOk();
        $response->assertJsonPath('data.meta.page', 2);
        $response->assertJsonPath('data.meta.last_page', 2);
        $response->assertJsonPath('data.rows.0.supplier_invoice_id', 'invoice-11');
    }

    private function admin(): User
    {
        $user = User::query()->create([
            'name' => 'Admin',
            'email' => 'admin@example.test',
            'password' => 'password123',
        ]);

        DB::table('actor_accesses')->insert([
            'actor_id' => (string) $user->getAuthIdentifier(),
            'role' => 'admin',
        ]);

        return $user;
    }

    private function seedSupplier(string $id, string $namaPtPengirim): void
    {
        DB::table('suppliers')->insert([
            'id' => $id,
            'nama_pt_pengirim' => $namaPtPengirim,
            'nama_pt_pengirim_normalized' => mb_strtolower($namaPtPengirim),
        ]);
    }

    private function seedInvoice(string $id, string $supplierId, string $shipmentDate, string $dueDate, int $grandTotal): void
    {
        DB::table('supplier_invoices')->insert([
            'id' => $id,
            'supplier_id' => $supplierId,
            'tanggal_pengiriman' => $shipmentDate,
            'jatuh_tempo' => $dueDate,
            'grand_total_rupiah' => $grandTotal,
        ]);
    }

    private function seedPayment(string $id, string $invoiceId, int $amount, string $paidAt, string $proofStatus): void
    {
        DB::table('supplier_payments')->insert([
            'id' => $id,
            'supplier_invoice_id' => $invoiceId,
            'amount_rupiah' => $amount,
            'paid_at' => $paidAt,
            'proof_status' => $proofStatus,
            'proof_storage_path' => null,
        ]);
    }
}
