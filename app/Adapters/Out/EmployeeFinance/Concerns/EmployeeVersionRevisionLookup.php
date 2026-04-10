<?php

declare(strict_types=1);

namespace App\Adapters\Out\EmployeeFinance\Concerns;

use Illuminate\Support\Facades\DB;

trait EmployeeVersionRevisionLookup
{
    private function nextRevisionNo(string $employeeId): int
    {
        $lastRevision = DB::table('employee_versions')
            ->where('employee_id', $employeeId)
            ->max('revision_no');

        return ((int) $lastRevision) + 1;
    }

    private function resolveUpdateEventName(string $beforeStatus, string $afterStatus): string
    {
        if ($beforeStatus !== 'inactive' && $afterStatus === 'inactive') {
            return 'employee_deactivated';
        }

        if ($beforeStatus !== 'active' && $afterStatus === 'active') {
            return 'employee_reactivated';
        }

        return 'employee_updated';
    }
}
