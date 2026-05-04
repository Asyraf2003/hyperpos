<?php

declare(strict_types=1);

namespace App\Adapters\Out\Expense;

use App\Ports\Out\Expense\ExpenseCategoryOptionReaderPort;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

final class DatabaseExpenseCategoryListPageQuery implements ExpenseCategoryOptionReaderPort
{
    /**
     * @return list<array{id:string,code:string,name:string,description:?string,is_active:bool}>
     */
    public function listRows(): array
    {
        $rows = $this->applyOrdering($this->baseSelect())->get();

        return array_map(
            static fn (object $row): array => [
                'id' => (string) $row->id,
                'code' => (string) $row->code,
                'name' => (string) $row->name,
                'description' => $row->description !== null ? (string) $row->description : null,
                'is_active' => (bool) $row->is_active,
            ],
            $rows->all(),
        );
    }

    /**
     * @return list<array{id:string,label:string}>
     */
    public function listActiveOptions(): array
    {
        $rows = DB::table('expense_categories')
            ->select(['id', 'code', 'name'])
            ->where('is_active', true)
            ->orderBy('name')
            ->orderBy('code')
            ->orderBy('id')
            ->get();

        return array_map(
            fn (object $row): array => [
                'id' => (string) $row->id,
                'label' => $this->toOptionLabel($row),
            ],
            $rows->all(),
        );
    }

    private function baseSelect(): Builder
    {
        return DB::table('expense_categories')
            ->select(['id', 'code', 'name', 'description', 'is_active']);
    }

    private function applyOrdering(Builder $query): Builder
    {
        return $query
            ->orderByDesc('is_active')
            ->orderBy('name')
            ->orderBy('code')
            ->orderBy('id');
    }

    private function toOptionLabel(object $row): string
    {
        return sprintf('%s (%s)', (string) $row->name, (string) $row->code);
    }
}
