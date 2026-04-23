<?php

declare(strict_types=1);

namespace App\Adapters\Out\Procurement\Concerns;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

trait SupplierTableBaseQuery
{
    private function baseTableQuery(): Builder
    {
        return DB::table('supplier_list_projection')
            ->select([
                'supplier_id as id',
                'nama_pt_pengirim',
                'invoice_count',
                'outstanding_rupiah',
                'invoice_unpaid_count',
                'last_shipment_date',
            ]);
    }
}
