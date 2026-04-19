<?php

declare(strict_types=1);

namespace App\Adapters\Out\EmployeeFinance;

use Illuminate\Support\Facades\DB;

final class DatabaseEmployeeListPageQuery
{
    /**
     * @return list<array{
     *     id: string,
     *     employee_name: string,
     *     phone: ?string,
     *     default_salary_amount: ?int,
     *     default_salary_amount_formatted: ?string,
     *     salary_basis_type: string,
     *     salary_basis_label: string,
     *     employment_status: string,
     *     employment_status_label: string
     * }>
     */
    public function all(): array
    {
        return DB::table('employees')
            ->select([
                'id',
                'employee_name',
                'phone',
                'default_salary_amount',
                'salary_basis_type',
                'employment_status',
            ])
            ->orderBy('employee_name')
            ->orderBy('created_at')
            ->get()
            ->map(function (object $row): array {
                $salaryBasisType = (string) $row->salary_basis_type;
                $employmentStatus = (string) $row->employment_status;
                $defaultSalaryAmount = $row->default_salary_amount !== null
                    ? (int) $row->default_salary_amount
                    : null;

                return [
                    'id' => (string) $row->id,
                    'employee_name' => (string) $row->employee_name,
                    'phone' => $row->phone !== null ? (string) $row->phone : null,
                    'default_salary_amount' => $defaultSalaryAmount,
                    'default_salary_amount_formatted' => $defaultSalaryAmount !== null
                        ? number_format($defaultSalaryAmount, 0, ',', '.')
                        : null,
                    'salary_basis_type' => $salaryBasisType,
                    'salary_basis_label' => $this->salaryBasisLabel($salaryBasisType),
                    'employment_status' => $employmentStatus,
                    'employment_status_label' => $this->employmentStatusLabel($employmentStatus),
                ];
            })
            ->values()
            ->all();
    }

    private function salaryBasisLabel(string $salaryBasisType): string
    {
        return match ($salaryBasisType) {
            'daily' => 'Harian',
            'weekly' => 'Mingguan',
            'monthly' => 'Bulanan',
            'manual' => 'Manual',
            default => ucfirst($salaryBasisType),
        };
    }

    private function employmentStatusLabel(string $employmentStatus): string
    {
        return match ($employmentStatus) {
            'active' => 'Aktif',
            'inactive' => 'Nonaktif',
            default => ucfirst($employmentStatus),
        };
    }
}
