<?php

declare(strict_types=1);

namespace App\Adapters\Out\EmployeeFinance;

use Illuminate\Support\Facades\DB;

final class DatabaseEmployeeDetailPageQuery
{
    public function __construct(
        private DatabaseEmployeeDebtSummaryByEmployeeQuery $debtSummaryQuery,
        private DatabaseEmployeeDebtRecordListByEmployeeQuery $debtRecordListQuery,
        private DatabaseEmployeeDebtPaymentListByEmployeeQuery $debtPaymentListQuery,
        private DatabaseEmployeePayrollSummaryByEmployeeQuery $payrollSummaryQuery,
        private DatabaseEmployeePayrollHistoryByEmployeeQuery $payrollHistoryQuery,
    ) {
    }

    public function findById(string $employeeId): ?array
    {
        $row = DB::table('employees')
            ->select(['id', 'name', 'phone', 'base_salary', 'pay_period', 'status'])
            ->where('id', $employeeId)
            ->first();

        if ($row === null) {
            return null;
        }

        $baseSalary = (int) $row->base_salary;
        $payPeriodValue = (string) $row->pay_period;
        $statusValue = (string) $row->status;

        return [
            'summary' => [
                'id' => (string) $row->id,
                'name' => (string) $row->name,
                'phone' => $row->phone !== null ? (string) $row->phone : null,
                'base_salary_amount' => $baseSalary,
                'base_salary_formatted' => number_format($baseSalary, 0, ',', '.'),
                'pay_period_value' => $payPeriodValue,
                'pay_period_label' => $this->payPeriodLabel($payPeriodValue),
                'status_value' => $statusValue,
                'status_label' => $this->statusLabel($statusValue),
            ],
            'debt' => [
                'summary' => $this->debtSummaryQuery->findByEmployeeId($employeeId),
                'records' => $this->debtRecordListQuery->findByEmployeeId($employeeId),
                'payments' => $this->debtPaymentListQuery->findByEmployeeId($employeeId),
            ],
            'payroll' => [
                'summary' => $this->payrollSummaryQuery->findByEmployeeId($employeeId),
                'records' => $this->payrollHistoryQuery->findByEmployeeId($employeeId),
            ],
        ];
    }

    private function payPeriodLabel(string $value): string
    {
        return match ($value) {
            'daily' => 'Harian',
            'weekly' => 'Mingguan',
            'monthly' => 'Bulanan',
            default => ucfirst($value),
        };
    }

    private function statusLabel(string $value): string
    {
        return match ($value) {
            'active' => 'Aktif',
            'inactive' => 'Nonaktif',
            default => ucfirst($value),
        };
    }
}
