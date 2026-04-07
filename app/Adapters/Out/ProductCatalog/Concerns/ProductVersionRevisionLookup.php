<?php

declare(strict_types=1);

namespace App\Adapters\Out\ProductCatalog\Concerns;

use Illuminate\Support\Facades\DB;

trait ProductVersionRevisionLookup
{
    private function nextRevisionNo(string $productId): int
    {
        $current = DB::table('product_versions')
            ->where('product_id', $productId)
            ->max('revision_no');

        return ((int) ($current ?? 0)) + 1;
    }
}
