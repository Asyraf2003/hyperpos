<?php

declare(strict_types=1);

namespace App\Adapters\Out\EmployeeFinance;

use App\Application\EmployeeFinance\DTO\EmployeeDebtTableQuery;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

final class DatabaseEmployeeDebtListPageQuery implements \App\Ports\Out\EmployeeFinance\EmployeeDebtTableReaderPort
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
                DB::table('employee_debts as debt_target')
                    ->select('debt_target.id')
                    ->whereColumn('debt_target.employee_id', 'employee_debts.employee_id')
                    ->orderByRaw("CASE WHEN debt_target.status = 'unpaid' THEN 0 ELSE 1 END")
                    ->orderByDesc('debt_target.created_at')
                    ->orderByDesc('debt_target.id')
                    ->limit(1),
                'debt_detail_id'
            )
            ->selectSub(
                DB::table('employee_debts as latest_unpaid_debts')
                    ->select('latest_unpaid_debts.id')
                    ->whereColumn('latest_unpaid_debts.employee_id', 'employee_debts.employee_id')
                    ->where('latest_unpaid_debts.status', 'unpaid')
                    ->orderByDesc('latest_unpaid_debts.created_at')
                    ->orderByDesc('latest_unpaid_debts.id')
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
            'rows' => collect($paginator->items())->map(function (object $row): array {
                $statusLabel = ((int) $row->active_debt_count) > 0 ? 'Aktif' : 'Lunas';

                return [
                    'employee_id' => (string) $row->employee_id,
                    'employee_name' => (string) $row->employee_name,
                    'debt_detail_id' => $row->debt_detail_id !== null
                        ? (string) $row->debt_detail_id
                        : null,
                    'latest_unpaid_debt_id' => $row->latest_unpaid_debt_id !== null
                        ? (string) $row->latest_unpaid_debt_id
                        : null,
                    'total_debt_records' => (int) $row->total_debt_records,
                    'total_debt_amount_formatted' => number_format((int) $row->total_debt_amount, 0, ',', '.'),
                    'total_remaining_balance_formatted' => number_format((int) $row->total_remaining_balance, 0, ',', '.'),
                    'active_debt_count' => (int) $row->active_debt_count,
                    'paid_debt_count' => (int) $row->paid_debt_count,
                    'status_label' => $statusLabel,
                    'latest_recorded_at' => Carbon::parse((string) $row->latest_recorded_at)->format('Y-m-d'),
                ];
            })->values()->all(),
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
