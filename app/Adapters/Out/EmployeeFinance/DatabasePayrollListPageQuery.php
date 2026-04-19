<?php

declare(strict_types=1);

namespace App\Adapters\Out\EmployeeFinance;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

final class DatabasePayrollListPageQuery
{
    /**
     * @return list<array{
     *     id: string,
     *     employee_id: string,
     *     employee_name: string,
     *     amount: int,
     *     amount_formatted: string,
     *     disbursement_date: string,
     *     mode_value: string,
     *     mode_label: string,
     *     notes: ?string
     * }>
     */
    public function latest(): array
    {
        return DB::table('payroll_disbursements')
            ->join('employees', 'employees.id', '=', 'payroll_disbursements.employee_id')
            ->select([
                'payroll_disbursements.id',
                'payroll_disbursements.employee_id',
                'employees.name as employee_name',
                'payroll_disbursements.amount',
                'payroll_disbursements.disbursement_date',
                'payroll_disbursements.mode',
                'payroll_disbursements.notes',
            ])
            ->orderByDesc('payroll_disbursements.disbursement_date')
            ->orderByDesc('payroll_disbursements.created_at')
            ->limit(50)
            ->get()
            ->map(function (object $row): array {
                $amount = (int) $row->amount;
                $modeValue = (string) $row->mode;

                return [
                    'id' => (string) $row->id,
                    'employee_id' => (string) $row->employee_id,
                    'employee_name' => (string) $row->employee_name,
                    'amount' => $amount,
                    'amount_formatted' => number_format($amount, 0, ',', '.'),
                    'disbursement_date' => Carbon::parse((string) $row->disbursement_date)->format('Y-m-d'),
                    'mode_value' => $modeValue,
                    'mode_label' => $this->modeLabel($modeValue),
                    'notes' => $row->notes !== null ? (string) $row->notes : null,
                ];
            })
            ->values()
            ->all();
    }

    private function modeLabel(string $modeValue): string
    {
        return match ($modeValue) {
            'daily' => 'Harian',
            'weekly' => 'Mingguan',
            'monthly' => 'Bulanan',
            default => ucfirst($modeValue),
        };
    }
}
