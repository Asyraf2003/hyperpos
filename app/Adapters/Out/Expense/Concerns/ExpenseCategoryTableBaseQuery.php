<?php

declare(strict_types=1);

namespace App\Adapters\Out\Expense\Concerns;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

trait ExpenseCategoryTableBaseQuery
{
    private function baseTableQuery(): Builder
    {
        return DB::table('expense_categories')->select([
            'id',
            'code',
            'name',
            'description',
            'is_active',
            'updated_at',
        ]);
    }
}
