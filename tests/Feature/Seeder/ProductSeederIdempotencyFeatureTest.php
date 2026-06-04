<?php

declare(strict_types=1);

namespace Tests\Feature\Seeder;

use Database\Seeders\CreateOnly\CreateMasterBasicSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

final class ProductSeederIdempotencyFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_only_basic_product_seed_is_idempotent(): void
    {
        $this->seed(CreateMasterBasicSeeder::class);

        $firstCount = $this->basicProductCount();
        $firstDistinctCodeCount = $this->basicProductDistinctCodeCount();

        $this->seed(CreateMasterBasicSeeder::class);

        $this->assertSame(10, $firstCount);
        $this->assertSame(10, $this->basicProductCount());
        $this->assertSame($firstDistinctCodeCount, $this->basicProductDistinctCodeCount());
        $this->assertSame($this->basicProductCount(), $this->basicProductDistinctCodeCount());
    }

    public function test_create_only_basic_product_seed_writes_expected_product_contract(): void
    {
        $this->seed(CreateMasterBasicSeeder::class);

        $product = DB::table('products')
            ->where('kode_barang', 'BASIC-P0001')
            ->first();

        $this->assertNotNull($product);
        $this->assertSame('prod-basic-001', $product->id);
        $this->assertSame('BASIC-P0001', $product->kode_barang);
        $this->assertSame('Barang Demo BASIC 001', $product->nama_barang);
        $this->assertSame('Merek BASIC 01', $product->merek);
        $this->assertSame(1, (int) $product->ukuran);
        $this->assertSame(17500, (int) $product->harga_jual);
        $this->assertSame('barang demo basic 001', $product->nama_barang_normalized);
        $this->assertSame('merek basic 01', $product->merek_normalized);
        $this->assertSame(6, (int) $product->reorder_point_qty);
        $this->assertSame(3, (int) $product->critical_threshold_qty);
        $this->assertNull($product->deleted_at);
    }

    private function basicProductCount(): int
    {
        return DB::table('products')
            ->where('kode_barang', 'like', 'BASIC-P%')
            ->count();
    }

    private function basicProductDistinctCodeCount(): int
    {
        return DB::table('products')
            ->where('kode_barang', 'like', 'BASIC-P%')
            ->distinct()
            ->count('kode_barang');
    }
}
