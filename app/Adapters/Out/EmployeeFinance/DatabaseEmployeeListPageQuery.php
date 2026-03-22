<?php

declare(strict_types=1);

namespace App\Adapters\Out\EmployeeFinance;

use Illuminate\Support\Facades\DB;

final class DatabaseEmployeeListPageQuery
{
    /**
     * @return list<array{
     *     id: string,
     *     name: string,
     *     phone: ?string,
     *     base_salary_amount: int,
     *     base_salary_formatted: string,
     *     pay_period_value: string,
     *     pay_period_label: string,
     *     status_value: string,
     *     status_label: string
     * }>
     */
    public function all(): array
    {
        return DB::table('employees')
            ->select(['id', 'name', 'phone', 'base_salary', 'pay_period', 'status'])
            ->orderBy('name')
            ->orderBy('created_at')
            ->get()
            ->map(function (object $row): array {
                $payPeriodValue = (string) $row->pay_period;
                $statusValue = (string) $row->status;
                $baseSalaryAmount = (int) $row->base_salary;

                return [
                    'id' => (string) $row->id,
                    'name' => (string) $row->name,
                    'phone' => $row->phone !== null ? (string) $row->phone : null,
                    'base_salary_amount' => $baseSalaryAmount,
                    'base_salary_formatted' => number_format($baseSalaryAmount, 0, ',', '.'),
                    'pay_period_value' => $payPeriodValue,
                    'pay_period_label' => $this->payPeriodLabel($payPeriodValue),
                    'status_value' => $statusValue,
                    'status_label' => $this->statusLabel($statusValue),
                ];
            })
            ->values()
            ->all();
    }

    private function payPeriodLabel(string $payPeriodValue): string
    {
        return match ($payPeriodValue) {
            'daily' => 'Harian',
            'weekly' => 'Mingguan',
            'monthly' => 'Bulanan',
            default => ucfirst($payPeriodValue),
        };
    }

    private function statusLabel(string $statusValue): string
    {
        return match ($statusValue) {
            'active' => 'Aktif',
            'inactive' => 'Nonaktif',
            default => ucfirst($statusValue),
        };
    }
}
