<?php

declare(strict_types=1);

namespace Tests\Support;

use Illuminate\Support\Facades\DB;

trait SeedsMinimalInventoryProductFixture
{
    private function seedInventoryProduct(
        string $id,
        ?string $kodeBarang = null,
        ?string $namaBarang = null,
        string $merek = 'General',
        ?int $ukuran = 100,
        int $hargaJual = 10000
    ): void {
        $kodeBarang ??= strtoupper(str_replace(['_', ' '], '-', $id));
        $namaBarang ??= 'Produk ' . $id;

        DB::table('products')->updateOrInsert(
            ['id' => $id],
            [
                'kode_barang' => $kodeBarang,
                'nama_barang' => $namaBarang,
                'nama_barang_normalized' => $this->normalize($namaBarang),
                'merek' => $merek,
                'merek_normalized' => $this->normalize($merek),
                'ukuran' => $ukuran,
                'harga_jual' => $hargaJual,
                'deleted_at' => null,
                'deleted_by_actor_id' => null,
                'delete_reason' => null,
            ]
        );
    }

    private function seedInventoryMovement(
        string $id,
        string $productId,
        string $movementType,
        string $sourceType,
        string $sourceId,
        string $tanggalMutasi,
        int $qtyDelta,
        int $unitCostRupiah
    ): void {
        DB::table('inventory_movements')->insert([
            'id' => $id,
            'product_id' => $productId,
            'movement_type' => $movementType,
            'source_type' => $sourceType,
            'source_id' => $sourceId,
            'tanggal_mutasi' => $tanggalMutasi,
            'qty_delta' => $qtyDelta,
            'unit_cost_rupiah' => $unitCostRupiah,
            'total_cost_rupiah' => $qtyDelta * $unitCostRupiah,
        ]);
    }

    private function normalize(string $value): string
    {
        $value = preg_replace('/\s+/', ' ', trim($value)) ?? trim($value);

        return mb_strtolower($value);
    }
}
