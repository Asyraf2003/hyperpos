<?php

declare(strict_types=1);

namespace Tests\Support;

use Illuminate\Support\Facades\DB;

trait SeedsMinimalProductFixture
{
    private function seedMinimalProduct(
        string $id = 'product-1',
        string $kodeBarang = 'KB-001',
        string $namaBarang = 'Produk Test',
        string $merek = 'General',
        ?int $ukuran = 100,
        int $hargaJual = 10000
    ): void {
        DB::table('products')->updateOrInsert(
            ['id' => $id],
            [
                'kode_barang' => $kodeBarang,
                'nama_barang' => $namaBarang,
                'nama_barang_normalized' => mb_strtolower(trim($namaBarang)),
                'merek' => $merek,
                'merek_normalized' => mb_strtolower(trim($merek)),
                'ukuran' => $ukuran,
                'harga_jual' => $hargaJual,
                'deleted_at' => null,
                'deleted_by_actor_id' => null,
                'delete_reason' => null,
            ]
        );
    }
}
