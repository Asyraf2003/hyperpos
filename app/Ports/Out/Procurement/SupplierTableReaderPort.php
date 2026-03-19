<?php

declare(strict_types=1);

namespace App\Ports\Out\Procurement;

use App\Application\Procurement\DTO\SupplierTableQuery;

interface SupplierTableReaderPort
{
    /**
     * @return array{
     *   rows:list<array{
     *     id:string,
     *     nama_pt_pengirim:string
     *   }>,
     *   meta:array{
     *     page:int,
     *     per_page:int,
     *     total:int,
     *     last_page:int,
     *     sort_by:string,
     *     sort_dir:string,
     *     filters:array{
     *       q:?string
     *     }
     *   }
     * }
     */
    public function search(SupplierTableQuery $query): array;
}
