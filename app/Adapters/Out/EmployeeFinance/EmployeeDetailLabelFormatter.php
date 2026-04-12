<?php

declare(strict_types=1);

namespace App\Adapters\Out\EmployeeFinance;

final class EmployeeDetailLabelFormatter
{
    public function salaryBasis(string $value): string
    {
        return match ($value) {
            'daily' => 'Harian',
            'weekly' => 'Mingguan',
            'monthly' => 'Bulanan',
            'manual' => 'Manual',
            default => ucfirst($value),
        };
    }

    public function employmentStatus(string $value): string
    {
        return match ($value) {
            'active' => 'Aktif',
            'inactive' => 'Nonaktif',
            default => ucfirst($value),
        };
    }

    public function event(string $value): string
    {
        return match ($value) {
            'employee_created' => 'Karyawan dibuat',
            'employee_updated' => 'Profil diperbarui',
            'employee_deactivated' => 'Karyawan dinonaktifkan',
            'employee_reactivated' => 'Karyawan diaktifkan kembali',
            default => ucfirst(str_replace('_', ' ', $value)),
        };
    }
}
