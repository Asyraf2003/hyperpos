<?php

declare(strict_types=1);

namespace Tests\Feature\Procurement;

use App\Application\Procurement\UseCases\UpdateSupplierHandler;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

final class UpdateSupplierFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_update_supplier_handler_can_rename_supplier(): void
    {
        $this->seedSupplier('supplier-1', 'PT Sumber Makmur');

        $result = app(UpdateSupplierHandler::class)->handle('supplier-1', '  PT Supplier Test  ');

        $this->assertFalse($result->isFailure());
        $this->assertSame('Supplier berhasil diperbarui.', $result->message());

        $this->assertDatabaseHas('suppliers', [
            'id' => 'supplier-1',
            'nama_pt_pengirim' => 'PT Supplier Test',
            'nama_pt_pengirim_normalized' => 'pt supplier test',
        ]);
    }

    public function test_update_supplier_handler_rejects_duplicate_normalized_supplier_name(): void
    {
        $this->seedSupplier('supplier-1', 'PT Sumber Makmur');
        $this->seedSupplier('supplier-2', 'PT Supplier Test');

        $result = app(UpdateSupplierHandler::class)->handle('supplier-2', '  pt   sumber   makmur ');

        $this->assertTrue($result->isFailure());
        $this->assertSame('Nama supplier sudah digunakan.', $result->message());

        $this->assertDatabaseHas('suppliers', [
            'id' => 'supplier-2',
            'nama_pt_pengirim' => 'PT Supplier Test',
            'nama_pt_pengirim_normalized' => 'pt supplier test',
        ]);
    }

    public function test_update_supplier_handler_returns_not_found_when_supplier_missing(): void
    {
        $result = app(UpdateSupplierHandler::class)->handle('missing-supplier', 'PT Baru');

        $this->assertTrue($result->isFailure());
        $this->assertSame('Supplier tidak ditemukan.', $result->message());
    }

    public function test_update_supplier_handler_does_not_change_procurement_history_snapshot(): void
    {
        $this->seedSupplier('supplier-1', 'PT Sumber Makmur');

        DB::table('supplier_invoices')->insert([
            'id' => 'invoice-1',
            'supplier_id' => 'supplier-1',
            'supplier_nama_pt_pengirim_snapshot' => 'PT Sumber Makmur',
            'tanggal_pengiriman' => '2026-03-15',
            'jatuh_tempo' => '2026-04-15',
            'grand_total_rupiah' => 100000,
        ]);

        $result = app(UpdateSupplierHandler::class)->handle('supplier-1', 'PT Supplier Baru');

        $this->assertFalse($result->isFailure());

        $this->assertDatabaseHas('suppliers', [
            'id' => 'supplier-1',
            'nama_pt_pengirim' => 'PT Supplier Baru',
            'nama_pt_pengirim_normalized' => 'pt supplier baru',
        ]);

        $this->assertDatabaseHas('supplier_invoices', [
            'id' => 'invoice-1',
            'supplier_id' => 'supplier-1',
            'supplier_nama_pt_pengirim_snapshot' => 'PT Sumber Makmur',
        ]);
    }

    private function seedSupplier(string $id, string $namaPtPengirim): void
    {
        DB::table('suppliers')->insert([
            'id' => $id,
            'nama_pt_pengirim' => $namaPtPengirim,
            'nama_pt_pengirim_normalized' => mb_strtolower($namaPtPengirim),
        ]);
    }
}
