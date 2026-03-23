<?php

declare(strict_types=1);

namespace App\Adapters\Out\Expense;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

final class DatabaseOperationalExpenseListPageQuery
{
    /**
     * @return list<array{
     *   id:string,
     *   expense_date:string,
     *   category_name:string,
     *   category_code:string,
     *   description:string,
     *   amount_rupiah:int,
     *   payment_method:string,
     *   reference_no:?string,
     *   status:string
     * }>
     */
    public function listRows(): array
    {
        $rows = $this->applyOrdering($this->baseSelect())->get();

        return array_map(
            static fn (object $row): array => [
                'id' => (string) $row->id,
                'expense_date' => (string) $row->expense_date,
                'category_name' => (string) $row->category_name,
                'category_code' => (string) $row->category_code,
                'description' => (string) $row->description,
                'amount_rupiah' => (int) $row->amount_rupiah,
                'payment_method' => (string) $row->payment_method,
                'reference_no' => $row->reference_no !== null ? (string) $row->reference_no : null,
                'status' => (string) $row->status,
            ],
            $rows->all(),
        );
    }

    private function baseSelect(): Builder
    {
        return DB::table('operational_expenses')
            ->join('expense_categories', 'expense_categories.id', '=', 'operational_expenses.category_id')
            ->select([
                'operational_expenses.id',
                'operational_expenses.expense_date',
                'operational_expenses.description',
                'operational_expenses.amount_rupiah',
                'operational_expenses.payment_method',
                'operational_expenses.reference_no',
                'operational_expenses.status',
                'expense_categories.name as category_name',
                'expense_categories.code as category_code',
                'operational_expenses.created_at',
            ]);
    }

    private function applyOrdering(Builder $query): Builder
    {
        return $query
            ->orderByDesc('operational_expenses.expense_date')
            ->orderByDesc('operational_expenses.created_at')
            ->orderByDesc('operational_expenses.id');
    }
}
