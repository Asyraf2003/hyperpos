<?php

declare(strict_types=1);

namespace App\Adapters\Out\EmployeeFinance;

use App\Core\EmployeeFinance\Employee\Employee;
use App\Ports\Out\EmployeeFinance\EmployeeWriterPort;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

final class DatabaseEmployeeWriterAdapter implements EmployeeWriterPort
{
    public function save(Employee $employee): void
    {
        $now = Carbon::now();
        $record = $this->toRecord($employee);

        DB::table('employees')->updateOrInsert(
            ['id' => $employee->getId()],
            array_merge($record, [
                'updated_at' => $now,
            ])
        );

        DB::table('employees')
            ->where('id', $employee->getId())
            ->whereNull('created_at')
            ->update(['created_at' => $now]);
    }

    /**
     * @return array<string, string|int|null>
     */
    private function toRecord(Employee $employee): array
    {
        return [
            'id' => $employee->getId(),
            'employee_name' => $employee->getEmployeeName(),
            'phone' => $employee->getPhone(),
            'salary_basis_type' => $employee->getSalaryBasisType()->value,
            'default_salary_amount' => $employee->getDefaultSalaryAmount()?->amount(),
            'employment_status' => $employee->getEmploymentStatus()->value,
            'started_at' => $employee->getStartedAt()?->format('Y-m-d'),
            'ended_at' => $employee->getEndedAt()?->format('Y-m-d'),
        ];
    }
}
