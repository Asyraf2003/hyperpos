<?php

declare(strict_types=1);

namespace App\Adapters\Out\Reporting\Queries\ServicePackageProfitBreakdown;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

final class PartsTotalSubquery
{
    public function query(): Builder
    {
        return DB::table('work_item_store_stock_lines')
            ->selectRaw('work_item_id, SUM(line_total_rupiah) as parts_total_rupiah')
            ->groupBy('work_item_id');
    }
}
