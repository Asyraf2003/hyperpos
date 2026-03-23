<?php

declare(strict_types=1);

namespace App\Adapters\Out\Expense\Concerns;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

trait OperationalExpenseTableBaseQuery
{
    private function baseTableQuery(): Builder
    {
        return DB::table('operational_expenses')->select([
            'id',
            'category_id',
            'category_name_snapshot',
            'category_code_snapshot',
            'expense_date',
            'description',
            'amount_rupiah',
            'payment_method',
            'status',
            'created_at',
        ]);
    }
}
