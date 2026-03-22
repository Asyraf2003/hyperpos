<?php

declare(strict_types=1);

namespace App\Adapters\Out\EmployeeFinance;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

final class DatabaseEmployeePayrollHistoryByEmployeeQuery
{
    public function findByEmployeeId(string $employeeId): array
    {
        return DB::table('payroll_disbursements')
            ->select(['id', 'amount', 'disbursement_date', 'mode', 'notes'])
            ->where('employee_id', $employeeId)
            ->orderByDesc('disbursement_date')
            ->orderByDesc('created_at')
            ->get()
            ->map(function (object $row): array {
                $amount = (int) $row->amount;
                $modeValue = (string) $row->mode;

                return [
                    'id' => (string) $row->id,
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

    private function modeLabel(string $value): string
    {
        return match ($value) {
            'daily' => 'Harian',
            'weekly' => 'Mingguan',
            'monthly' => 'Bulanan',
            default => ucfirst($value),
        };
    }
}
