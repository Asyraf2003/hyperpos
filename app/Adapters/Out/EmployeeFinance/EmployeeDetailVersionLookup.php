<?php

declare(strict_types=1);

namespace App\Adapters\Out\EmployeeFinance;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

final class EmployeeDetailVersionLookup
{
    public function timelineRows(string $employeeId): Collection
    {
        return DB::table('employee_versions')
            ->select([
                'id',
                'revision_no',
                'event_name',
                'changed_by_actor_id',
                'change_reason',
                'changed_at',
                'snapshot_json',
            ])
            ->where('employee_id', $employeeId)
            ->orderByDesc('revision_no')
            ->get();
    }

    public function createdVersion(string $employeeId): ?object
    {
        return DB::table('employee_versions')
            ->where('employee_id', $employeeId)
            ->where('event_name', 'employee_created')
            ->orderBy('revision_no')
            ->first($this->columns());
    }

    public function firstRecordedVersion(string $employeeId): ?object
    {
        return DB::table('employee_versions')
            ->where('employee_id', $employeeId)
            ->orderBy('revision_no')
            ->first($this->columns());
    }

    private function columns(): array
    {
        return [
            'revision_no',
            'event_name',
            'changed_by_actor_id',
            'change_reason',
            'changed_at',
            'snapshot_json',
        ];
    }
}
