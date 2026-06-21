<?php

declare(strict_types=1);

namespace App\Adapters\Out\Reporting\Queries\ServicePackageProfitBreakdown;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

final class RefundComponentSubqueries
{
    public function product(): Builder
    {
        return DB::table('refund_component_allocations')
            ->whereIn('component_type', [
                'product_only_work_item',
                'service_store_stock_part',
            ])
            ->selectRaw('work_item_id, SUM(refunded_amount_rupiah) as refunded_product_component_rupiah')
            ->groupBy('work_item_id');
    }

    public function service(): Builder
    {
        return DB::table('refund_component_allocations')
            ->where('component_type', 'service_fee')
            ->selectRaw('work_item_id, SUM(refunded_amount_rupiah) as refunded_service_component_rupiah')
            ->groupBy('work_item_id');
    }
}
