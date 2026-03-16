<?php

declare(strict_types=1);

namespace App\Adapters\Out\EmployeeFinance;

use App\Core\EmployeeFinance\Employee\Employee;
use App\Core\EmployeeFinance\Employee\EmployeeStatus;
use App\Core\EmployeeFinance\Employee\PayPeriod;
use App\Core\Shared\ValueObjects\Money;
use App\Ports\Out\EmployeeFinance\EmployeeReaderPort;
use Illuminate\Support\Facades\DB;

final class DatabaseEmployeeReaderAdapter implements EmployeeReaderPort
{
    public function findById(string $id): ?Employee
    {
        $row = DB::table('employees')
            ->select(['id', 'name', 'phone', 'base_salary', 'pay_period', 'status'])
            ->where('id', $id)
            ->first();

        if ($row === null) {
            return null;
        }

        return new Employee(
            (string) $row->id,
            (string) $row->name,
            $row->phone !== null ? (string) $row->phone : null,
            Money::fromInt((int) $row->base_salary),
            PayPeriod::from((string) $row->pay_period),
            EmployeeStatus::from((string) $row->status)
        );
    }
}
