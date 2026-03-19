<?php

declare(strict_types=1);

namespace App\Adapters\Out\Procurement\Concerns;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

trait SupplierTableBaseQuery
{
    private function baseTableQuery(): Builder
    {
        return DB::table('suppliers')
            ->select([
                'suppliers.id',
                'suppliers.nama_pt_pengirim',
            ]);
    }
}
