<?php

declare(strict_types=1);

namespace App\Adapters\Out\EmployeeFinance;

use App\Core\EmployeeFinance\Employee\Employee;
use App\Core\EmployeeFinance\Employee\EmployeeStatus;
use App\Core\EmployeeFinance\Employee\PayPeriod;
use App\Core\Shared\ValueObjects\Money;
use App\Ports\Out\EmployeeFinance\EmployeeReaderPort;
use DateTimeImmutable;
use Illuminate\Support\Facades\DB;

final class DatabaseEmployeeReaderAdapter implements EmployeeReaderPort
{
    public function findById(string $id): ?Employee
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
            ->where('id', $id)
            ->first();

        if ($row === null) {
            return null;
        }

        return new Employee(
            (string) $row->id,
            (string) $row->employee_name,
            $row->phone !== null ? (string) $row->phone : null,
            $this->toNullableMoney($row->default_salary_amount),
            PayPeriod::from((string) $row->salary_basis_type),
            EmployeeStatus::from((string) $row->employment_status),
            $this->parseOptionalDate($row->started_at),
            $this->parseOptionalDate($row->ended_at),
        );
    }

    private function toNullableMoney(mixed $value): ?Money
    {
        if ($value === null) {
            return null;
        }

        $amount = (int) $value;

        if ($amount <= 0) {
            return null;
        }

        return Money::fromInt($amount);
    }

    private function parseOptionalDate(mixed $value): ?DateTimeImmutable
    {
        if ($value === null || $value === '') {
            return null;
        }

        return new DateTimeImmutable((string) $value);
    }
}
