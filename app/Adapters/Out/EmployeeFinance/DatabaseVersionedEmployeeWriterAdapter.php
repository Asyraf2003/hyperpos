<?php

declare(strict_types=1);

namespace App\Adapters\Out\EmployeeFinance;

use App\Adapters\Out\EmployeeFinance\Concerns\EmployeeVersionRevisionLookup;
use App\Adapters\Out\EmployeeFinance\Concerns\EmployeeWritePayloads;
use App\Adapters\Out\EmployeeFinance\Concerns\PersistsVersionedEmployeeWrites;
use App\Adapters\Out\EmployeeFinance\Concerns\RecordsEmployeeHistory;
use App\Application\EmployeeFinance\Context\EmployeeChangeContext;
use App\Core\EmployeeFinance\Employee\Employee;
use App\Ports\Out\ClockPort;
use App\Ports\Out\EmployeeFinance\EmployeeWriterPort;
use App\Ports\Out\UuidPort;
use Illuminate\Support\Facades\DB;

final class DatabaseVersionedEmployeeWriterAdapter implements EmployeeWriterPort
{
    use PersistsVersionedEmployeeWrites;
    use EmployeeVersionRevisionLookup;
    use EmployeeWritePayloads;
    use RecordsEmployeeHistory;

    public function __construct(
        private readonly UuidPort $uuid,
        private readonly ClockPort $clock,
        private readonly EmployeeChangeContext $changeContext,
    ) {
    }

    public function save(Employee $employee): void
    {
        $existing = DB::table('employees')
            ->where('id', $employee->getId())
            ->first();

        if ($existing === null) {
            $this->persistCreatedEmployee($employee);
            return;
        }

        $this->persistUpdatedEmployee($employee, $existing);
    }
}
