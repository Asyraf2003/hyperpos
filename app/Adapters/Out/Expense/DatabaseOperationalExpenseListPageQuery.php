<?php

declare(strict_types=1);

namespace App\Adapters\Out\Expense;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

final class DatabaseOperationalExpenseListPageQuery
{
    /**
     * @return list<array{
     * id:string,
     * expense_date:string,
     * category_name:string,
     * category_code:string,
     * description:string,
     * amount_rupiah:int,
     * payment_method:string,
     * reference_no:?string,
     * status:string,
     * status_label:string,
     * status_badge_class:string
     * }>
     */
    public function listRows(): array
    {
        $rows = $this->applyOrdering($this->baseSelect())->get();

        return array_map(
            fn (object $row): array => [
                'id' => (string) $row->id,
                'expense_date' => (string) $row->expense_date,
                'category_name' => (string) $row->category_name_snapshot,
                'category_code' => (string) $row->category_code_snapshot,
                'description' => (string) $row->description,
                'amount_rupiah' => (int) $row->amount_rupiah,
                'payment_method' => (string) $row->payment_method,
                'reference_no' => $row->reference_no !== null ? (string) $row->reference_no : null,
                'status' => (string) $row->status,
                'status_label' => $this->statusLabel((string) $row->status),
                'status_badge_class' => $this->statusBadgeClass((string) $row->status),
            ],
            $rows->all(),
        );
    }

    private function baseSelect(): Builder
    {
        return DB::table('operational_expenses')
            ->select([
                'id',
                'expense_date',
                'category_name_snapshot',
                'category_code_snapshot',
                'description',
                'amount_rupiah',
                'payment_method',
                'reference_no',
                'status',
                'created_at',
            ]);
    }

    private function applyOrdering(Builder $query): Builder
    {
        return $query
            ->orderByDesc('expense_date')
            ->orderByDesc('created_at')
            ->orderByDesc('id');
    }

    private function statusLabel(string $status): string
    {
        return match ($status) {
            'posted' => 'Posted',
            'draft' => 'Draft',
            'cancelled' => 'Cancelled',
            default => ucfirst($status),
        };
    }

    private function statusBadgeClass(string $status): string
    {
        return match ($status) {
            'posted' => 'bg-light-success text-success',
            'draft' => 'bg-light-warning text-warning',
            'cancelled' => 'bg-light-danger text-danger',
            default => 'bg-light-secondary text-secondary',
        };
    }
}
