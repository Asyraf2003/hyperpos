<?php

declare(strict_types=1);

namespace App\Adapters\Out\EmployeeFinance;

use App\Application\EmployeeFinance\DTO\EmployeeTableQuery;
use App\Ports\Out\EmployeeFinance\EmployeeTableReaderPort;
use Illuminate\Support\Facades\DB;

final class DatabaseEmployeeTableReaderAdapter implements EmployeeTableReaderPort
{
    public function search(EmployeeTableQuery $query): array
    {
        $builder = DB::table('employees')->select(['id', 'name', 'phone', 'base_salary', 'pay_period', 'status']);

        if ($query->q() !== null) {
            foreach (preg_split('/\s+/', $query->q()) ?: [] as $term) {
                $builder->where(function ($where) use ($term): void {
                    $like = '%' . $term . '%';
                    $where->where('name', 'like', $like)->orWhere('phone', 'like', $like)
                        ->orWhere('pay_period', 'like', $like)->orWhere('status', 'like', $like);
                });
            }
        }

        $paginator = $builder->orderBy($query->sortBy(), $query->sortDir())
            ->paginate($query->perPage(), ['*'], 'page', $query->page());

        return [
            'rows' => collect($paginator->items())->map(fn (object $row): array => [
                'id' => (string) $row->id,
                'name' => (string) $row->name,
                'phone' => $row->phone !== null ? (string) $row->phone : null,
                'base_salary_amount' => (int) $row->base_salary,
                'base_salary_formatted' => number_format((int) $row->base_salary, 0, ',', '.'),
                'pay_period_value' => (string) $row->pay_period,
                'pay_period_label' => $this->payPeriodLabel((string) $row->pay_period),
                'status_value' => (string) $row->status,
                'status_label' => $this->statusLabel((string) $row->status),
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

    private function payPeriodLabel(string $value): string
    {
        return match ($value) { 'daily' => 'Harian', 'weekly' => 'Mingguan', 'monthly' => 'Bulanan', default => ucfirst($value) };
    }

    private function statusLabel(string $value): string
    {
        return match ($value) { 'active' => 'Aktif', 'inactive' => 'Nonaktif', default => ucfirst($value) };
    }
}
