<?php

declare(strict_types=1);

namespace App\Ports\Out\ProductCatalog;

interface ProductDetailReaderPort
{
    /**
     * @return array{
     *   product:array{
     *     id:string,
     *     kode_barang:?string,
     *     nama_barang:string,
     *     merek:string,
     *     ukuran:?int,
     *     harga_jual:int
     *   },
     *   initial_identity:?array{
     *     kode_barang:?string,
     *     nama_barang:string,
     *     merek:string,
     *     ukuran:?int,
     *     harga_jual:int,
     *     changed_at:string
     *   },
     *   has_identity_changes:bool
     * }|null
     */
    public function getDetail(string $productId): ?array;

    /**
     * @return list<array{
     *   revision_no:int,
     *   event_name:string,
     *   changed_at:string,
     *   changed_by_actor_id:?string,
     *   change_reason:?string,
     *   snapshot:array<string, mixed>
     * }>
     */
    public function getVersionTimeline(string $productId): array;
}
