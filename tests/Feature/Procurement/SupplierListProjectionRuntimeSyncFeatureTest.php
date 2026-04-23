<?php

declare(strict_types=1);

namespace Tests\Feature\Procurement;

use App\Application\Procurement\UseCases\CreateSupplierInvoiceFlowHandler;
use App\Application\Procurement\UseCases\RecordSupplierPaymentHandler;
use App\Application\Procurement\UseCases\UpdateSupplierHandler;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

final class SupplierListProjectionRuntimeSyncFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_supplier_list_projection_updates_after_create_invoice_and_payment(): void
    {
        $this->seedSupplier('supplier-1', 'PT Runtime Supplier');

        $create = app(CreateSupplierInvoiceFlowHandler::class)->handle(
            nomorFaktur: 'INV-RUNTIME-001',
            pt: 'PT Runtime Supplier',
            tglKirim: '2026-03-15',
            lines: [
                [
                    'product_id' => 'product-1',
                    'qty' => 2,
                    'harga_beli_rupiah' => 50000,
                ],
            ],
            autoRec: false,
            tglTerima: null,
            performedByActorId: 'admin-1',
            performedByActorRole: 'admin',
            sourceChannel: 'test',
        );

        $this->assertTrue($create->isSuccess());

        $projectionAfterCreate = DB::table('supplier_list_projection')
            ->where('supplier_id', 'supplier-1')
            ->first();

        $this->assertNotNull($projectionAfterCreate);
        $this->assertSame('PT Runtime Supplier', $projectionAfterCreate->nama_pt_pengirim);
        $this->assertSame(1, (int) $projectionAfterCreate->invoice_count);
        $this->assertSame(100000, (int) $projectionAfterCreate->outstanding_rupiah);
        $this->assertSame(1, (int) $projectionAfterCreate->invoice_unpaid_count);
        $this->assertSame('2026-03-15', (string) $projectionAfterCreate->last_shipment_date);

        $payment = app(RecordSupplierPaymentHandler::class)->handle(
            supplierInvoiceId: (string) $create->data()['id'],
            amountRupiah: 40000,
            paidAt: '2026-03-16',
            performedByActorId: 'admin-1',
        );

        $this->assertTrue($payment->isSuccess());

        $projectionAfterPayment = DB::table('supplier_list_projection')
            ->where('supplier_id', 'supplier-1')
            ->first();

        $this->assertNotNull($projectionAfterPayment);
        $this->assertSame(60000, (int) $projectionAfterPayment->outstanding_rupiah);
        $this->assertSame(1, (int) $projectionAfterPayment->invoice_unpaid_count);
    }

    public function test_supplier_list_projection_updates_after_supplier_rename(): void
    {
        $this->seedSupplier('supplier-1', 'PT Nama Lama');
        $this->syncSupplierListProjectionForTest('supplier-1');

        $result = app(UpdateSupplierHandler::class)->handle('supplier-1', 'PT Nama Baru');

        $this->assertTrue($result->isSuccess());

        $projection = DB::table('supplier_list_projection')
            ->where('supplier_id', 'supplier-1')
            ->first();

        $this->assertNotNull($projection);
        $this->assertSame('PT Nama Baru', $projection->nama_pt_pengirim);
    }

    private function seedSupplier(string $id, string $namaPtPengirim): void
    {
        DB::table('suppliers')->insert([
            'id' => $id,
            'nama_pt_pengirim' => $namaPtPengirim,
            'nama_pt_pengirim_normalized' => mb_strtolower($namaPtPengirim),
        ]);

        DB::table('actor_accesses')->insert([
            'actor_id' => 'admin-1',
            'role' => 'admin',
        ]);
    }

    private function seedProduct(string $id, string $namaBarang, int $hargaJual = 100000): void
    {
        DB::table('products')->insert([
            'id' => $id,
            'kode_barang' => strtoupper(str_replace('-', '_', $id)),
            'nama_barang' => $namaBarang,
            'merek' => 'Runtime',
            'ukuran' => 'std',
            'harga_jual' => $hargaJual,
        ]);

        DB::table('product_inventories')->insert([
            'product_id' => $id,
            'stok_saat_ini' => 0,
            'avg_cost' => 0,
        ]);
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->seedProduct('product-1', 'Produk Runtime');
    }
}
