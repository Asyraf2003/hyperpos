<?php

declare(strict_types=1);

namespace Tests\Feature\Procurement;

use App\Adapters\Out\Persistence\Eloquent\IdentityAccess\EloquentUser as User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

final class SupplierInvoiceTaxRoundingResidueUiFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_page_renders_tax_rounding_residue_confirmation_contract(): void
    {
        $response = $this->actingAs($this->user('admin'))
            ->get(route('admin.procurement.supplier-invoices.create'));

        $response->assertOk();
        $response->assertSee('name="tax_rounding_residue_confirmed"', false);
        $response->assertSee('value="0"', false);
        $response->assertSee('data-tax-rounding-residue-confirmed-input', false);
        $response->assertSee('data-tax-rounding-residue-message', false);
        $response->assertSee(
            'Total setelah pajak tidak habis dibagi qty, sehingga modal per pcs akan dibulatkan dan selisih pembulatan akan dicatat. Lanjutkan?'
        );
    }

    public function test_edit_page_renders_tax_rounding_residue_confirmation_contract(): void
    {
        $this->seedEditableInvoice();

        $response = $this->actingAs($this->user('admin'))
            ->get(route('admin.procurement.supplier-invoices.edit', [
                'supplierInvoiceId' => 'invoice-ui-rounding-1',
            ]));

        $response->assertOk();
        $response->assertSee('name="tax_rounding_residue_confirmed"', false);
        $response->assertSee('value="0"', false);
        $response->assertSee('data-tax-rounding-residue-confirmed-input', false);
        $response->assertSee('data-tax-rounding-residue-message', false);
        $response->assertSee(
            'Total setelah pajak tidak habis dibagi qty, sehingga modal per pcs akan dibulatkan dan selisih pembulatan akan dicatat. Lanjutkan?'
        );
    }

    private function seedEditableInvoice(): void
    {
        DB::table('suppliers')->insert([
            'id' => 'supplier-ui-rounding-1',
            'nama_pt_pengirim' => 'PT UI Rounding',
            'nama_pt_pengirim_normalized' => 'pt ui rounding',
        ]);

        DB::table('products')->insert([
            'id' => 'product-ui-rounding-1',
            'kode_barang' => 'UIR-001',
            'nama_barang' => 'Barang UI Rounding',
            'merek' => 'Federal',
            'ukuran' => 90,
            'harga_jual' => 15000,
        ]);

        DB::table('supplier_invoices')->insert([
            'id' => 'invoice-ui-rounding-1',
            'supplier_id' => 'supplier-ui-rounding-1',
            'supplier_nama_pt_pengirim_snapshot' => 'PT UI Rounding',
            'nomor_faktur' => 'INV-UI-ROUNDING',
            'nomor_faktur_normalized' => 'inv-ui-rounding',
            'document_kind' => 'invoice',
            'lifecycle_status' => 'active',
            'tanggal_pengiriman' => '2026-03-12',
            'jatuh_tempo' => '2026-04-12',
            'subtotal_before_tax_rupiah' => 300,
            'grand_total_rupiah' => 300,
            'last_revision_no' => 1,
        ]);

        DB::table('supplier_invoice_lines')->insert([
            'id' => 'invoice-ui-rounding-line-1',
            'supplier_invoice_id' => 'invoice-ui-rounding-1',
            'revision_no' => 1,
            'is_current' => true,
            'line_no' => 1,
            'product_id' => 'product-ui-rounding-1',
            'product_kode_barang_snapshot' => 'UIR-001',
            'product_nama_barang_snapshot' => 'Barang UI Rounding',
            'product_merek_snapshot' => 'Federal',
            'product_ukuran_snapshot' => 90,
            'qty_pcs' => 3,
            'line_total_rupiah' => 300,
            'unit_cost_rupiah' => 100,
        ]);
    }

    private function user(string $role): User
    {
        $user = User::query()->create([
            'name' => 'Test',
            'email' => $role . '-tax-rounding-ui@example.test',
            'password' => 'password123',
        ]);

        DB::table('actor_accesses')->insert([
            'actor_id' => (string) $user->getAuthIdentifier(),
            'role' => $role,
        ]);

        return $user;
    }
}
