<?php

declare(strict_types=1);

namespace App\Adapters\Out\EmployeeFinance;

use Illuminate\Support\Facades\DB;

final class DatabaseEmployeeDetailPageQuery
{
    public function findById(string $employeeId): ?array
    {
        $row = DB::table('employees')
            ->select([
                'id',
                'employee_name',
                'phone',
                'salary_basis_type',
                'default_salary_amount',
                'employment_status',
                'started_at',
                'ended_at',
            ])
            ->where('id', $employeeId)
            ->first();

        if ($row === null) {
            return null;
        }

        $defaultSalaryAmount = $row->default_salary_amount !== null ? (int) $row->default_salary_amount : null;
        $salaryBasisType = (string) $row->salary_basis_type;
        $employmentStatus = (string) $row->employment_status;

        return [
            'summary' => [
                'id' => (string) $row->id,
                'employee_name' => (string) $row->employee_name,
                'phone' => $row->phone !== null ? (string) $row->phone : null,
                'salary_basis_type' => $salaryBasisType,
                'salary_basis_label' => $this->salaryBasisLabel($salaryBasisType),
                'default_salary_amount' => $defaultSalaryAmount,
                'default_salary_amount_formatted' => $defaultSalaryAmount !== null
                    ? number_format($defaultSalaryAmount, 0, ',', '.')
                    : null,
                'employment_status' => $employmentStatus,
                'employment_status_label' => $this->employmentStatusLabel($employmentStatus),
                'started_at' => $row->started_at !== null ? (string) $row->started_at : null,
                'ended_at' => $row->ended_at !== null ? (string) $row->ended_at : null,
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
