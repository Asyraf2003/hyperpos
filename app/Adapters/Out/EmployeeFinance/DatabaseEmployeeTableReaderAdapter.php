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
        $builder = DB::table('employees')->select([
            'id',
            'employee_name',
            'phone',
            'salary_basis_type',
            'default_salary_amount',
            'employment_status',
        ]);

        if ($query->q() !== null) {
            foreach (preg_split('/\s+/', $query->q()) ?: [] as $term) {
                $builder->where(function ($where) use ($term): void {
                    $like = '%' . $term . '%';

                    $where->where('employee_name', 'like', $like)
                        ->orWhere('phone', 'like', $like)
                        ->orWhere('salary_basis_type', 'like', $like)
                        ->orWhere('employment_status', 'like', $like);
                });
            }
        }

        $paginator = $builder->orderBy($query->sortBy(), $query->sortDir())
            ->paginate($query->perPage(), ['*'], 'page', $query->page());

        return [
            'rows' => collect($paginator->items())->map(fn (object $row): array => [
                'id' => (string) $row->id,
                'employee_name' => (string) $row->employee_name,
                'phone' => $row->phone !== null ? (string) $row->phone : null,
                'salary_basis_type' => (string) $row->salary_basis_type,
                'salary_basis_label' => $this->salaryBasisLabel((string) $row->salary_basis_type),
                'default_salary_amount' => $row->default_salary_amount !== null ? (int) $row->default_salary_amount : null,
                'default_salary_amount_formatted' => $row->default_salary_amount !== null
                    ? number_format((int) $row->default_salary_amount, 0, ',', '.')
                    : null,
                'employment_status' => (string) $row->employment_status,
                'employment_status_label' => $this->employmentStatusLabel((string) $row->employment_status),
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

    private function salaryBasisLabel(string $value): string
    {
        return match ($value) {
            'daily' => 'Harian',
            'weekly' => 'Mingguan',
            'monthly' => 'Bulanan',
            'manual' => 'Manual',
            default => ucfirst($value),
        };
    }

    private function employmentStatusLabel(string $value): string
    {
        return match ($value) {
            'active' => 'Aktif',
            'inactive' => 'Nonaktif',
            default => ucfirst($value),
        };
    }
}
