<?php

declare(strict_types=1);

namespace App\Ports\Out\ProductCatalog;

use App\Application\ProductCatalog\DTO\ProductTableQuery;

interface ProductTableReaderPort
{
    /**
     * @return array{
     *   rows:list<array{
     *     id:string,
     *     kode_barang:?string,
     *     nama_barang:string,
     *     merek:string,
     *     ukuran:?int,
     *     harga_jual:int,
     *     stok_saat_ini:int
     *   }>,
     *   meta:array{
     *     page:int,
     *     per_page:int,
     *     total:int,
     *     last_page:int,
     *     sort_by:string,
     *     sort_dir:string,
     *     filters:array{
     *       q:?string,
     *       merek:?string,
     *       ukuran_min:?int,
     *       ukuran_max:?int,
     *       harga_min:?int,
     *       harga_max:?int
     *     }
     *   }
     * }
     */
    public function search(ProductTableQuery $query): array;
}
