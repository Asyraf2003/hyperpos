<?php

declare(strict_types=1);

namespace App\Adapters\Out\EmployeeFinance;

use App\Core\EmployeeFinance\Employee\Employee;
use App\Ports\Out\EmployeeFinance\EmployeeWriterPort;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

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

        // Pastikan created_at terisi jika ini data baru (updateOrInsert tidak otomatis mengisi created_at)
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
            'name' => $employee->getName(),
            'phone' => $employee->getPhone(),
            'base_salary' => $employee->getBaseSalary()->amount(),
            'pay_period' => $employee->getPayPeriod()->value,
            'status' => $employee->getStatus()->value,
        ];
    }
}
