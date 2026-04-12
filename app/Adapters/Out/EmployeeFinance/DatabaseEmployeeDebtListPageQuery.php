<?php

declare(strict_types=1);

namespace App\Adapters\Out\EmployeeFinance;

use App\Application\EmployeeFinance\DTO\EmployeeDebtTableQuery;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

final class DatabaseEmployeeDebtListPageQuery
{
    public function search(EmployeeDebtTableQuery $query): array
    {
        $builder = DB::table('employee_debts')
            ->join('employees', 'employees.id', '=', 'employee_debts.employee_id')
            ->select([
                'employee_debts.employee_id',
                'employees.employee_name as employee_name',
            ])
            ->selectSub(
                DB::table('employee_debts as latest_unpaid_debts')
                    ->select('latest_unpaid_debts.id')
                    ->whereColumn('latest_unpaid_debts.employee_id', 'employees.id')
                    ->where('latest_unpaid_debts.status', 'unpaid')
                    ->orderByDesc('latest_unpaid_debts.created_at')
                    ->limit(1),
                'latest_unpaid_debt_id'
            )
            ->selectRaw('COUNT(*) as total_debt_records')
            ->selectRaw('SUM(employee_debts.total_debt) as total_debt_amount')
            ->selectRaw('SUM(employee_debts.remaining_balance) as total_remaining_balance')
            ->selectRaw("SUM(CASE WHEN employee_debts.status = 'unpaid' THEN 1 ELSE 0 END) as active_debt_count")
            ->selectRaw("SUM(CASE WHEN employee_debts.status = 'paid' THEN 1 ELSE 0 END) as paid_debt_count")
            ->selectRaw('MAX(employee_debts.created_at) as latest_recorded_at');

        if ($query->q() !== null) {
            foreach (preg_split('/\s+/', $query->q()) ?: [] as $term) {
                $builder->where('employees.employee_name', 'like', '%'.$term.'%');
            }
        }

        $column = match ($query->sortBy()) {
            'employee_name' => 'employee_name',
            'total_debt_records' => 'total_debt_records',
            'total_debt_amount' => 'total_debt_amount',
            'total_remaining_balance' => 'total_remaining_balance',
            default => 'latest_recorded_at',
        };

        $paginator = $builder->groupBy('employee_debts.employee_id', 'employees.employee_name')
            ->orderBy($column, $query->sortDir())
            ->orderBy('employee_name')
            ->paginate($query->perPage(), ['*'], 'page', $query->page());

        return [
            'rows' => collect($paginator->items())->map(fn (object $row): array => [
                'employee_id' => (string) $row->employee_id,
                'employee_name' => (string) $row->employee_name,
                'latest_unpaid_debt_id' => $row->latest_unpaid_debt_id !== null
                    ? (string) $row->latest_unpaid_debt_id
                    : null,
                'total_debt_records' => (int) $row->total_debt_records,
                'total_debt_amount_formatted' => number_format((int) $row->total_debt_amount, 0, ',', '.'),
                'total_remaining_balance_formatted' => number_format((int) $row->total_remaining_balance, 0, ',', '.'),
                'active_debt_count' => (int) $row->active_debt_count,
                'paid_debt_count' => (int) $row->paid_debt_count,
                'latest_recorded_at' => Carbon::parse((string) $row->latest_recorded_at)->format('Y-m-d'),
            ])->values()->all(),
            'meta' => [
                'page' => $paginator->currentPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'last_page' => $paginator->lastPage(),
                'sort_by' => $query->sortBy(),
                'sort_dir' => $query->sortDir(),
                'filters' => ['q' => $query->q()],
            ],
        ];
    }
}
