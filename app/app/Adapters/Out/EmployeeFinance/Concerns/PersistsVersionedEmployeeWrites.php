<?php

declare(strict_types=1);

namespace App\Adapters\Out\EmployeeFinance\Concerns;

use App\Core\EmployeeFinance\Employee\Employee;
use Illuminate\Support\Facades\DB;

trait PersistsVersionedEmployeeWrites
{
    private function persistCreatedEmployee(Employee $employee): void
    {
        $revisionNo = 1;
        $occurredAt = $this->clock->now();
        $context = $this->changeContext->snapshot();
        $snapshot = $this->toSnapshot($employee);
        $eventName = 'employee_created';
        $auditEventId = $this->uuid->generate();

        DB::table('employees')->insert($this->toCreateEmployeeRecord($employee, $occurredAt));
        DB::table('employee_versions')->insert(
            $this->toVersionRecord($employee->getId(), $revisionNo, $eventName, $occurredAt, $context, $snapshot)
        );
        DB::table('audit_events')->insert(
            $this->toAuditEventRecord($auditEventId, $employee->getId(), $revisionNo, $eventName, $occurredAt, $context, $snapshot)
        );
        DB::table('audit_event_snapshots')->insert(
            $this->toAuditSnapshotRecord($auditEventId, 'after', $snapshot, $occurredAt)
        );

        $this->changeContext->clear();
    }

    private function persistUpdatedEmployee(Employee $employee, object $existing): void
    {
        $revisionNo = $this->nextRevisionNo($employee->getId());
        $occurredAt = $this->clock->now();
        $context = $this->changeContext->snapshot();
        $beforeSnapshot = $this->snapshotFromRow($existing);
        $afterSnapshot = $this->toSnapshot($employee);
        $eventName = $this->resolveUpdateEventName($beforeSnapshot['employment_status'], $afterSnapshot['employment_status']);
        $auditEventId = $this->uuid->generate();

        DB::table('employees')
            ->where('id', $employee->getId())
            ->update($this->toUpdateEmployeeRecord($employee, $occurredAt));

        DB::table('employee_versions')->insert(
            $this->toVersionRecord($employee->getId(), $revisionNo, $eventName, $occurredAt, $context, $afterSnapshot)
        );
        DB::table('audit_events')->insert(
            $this->toAuditEventRecord($auditEventId, $employee->getId(), $revisionNo, $eventName, $occurredAt, $context, $afterSnapshot)
        );
        DB::table('audit_event_snapshots')->insert([
            $this->toAuditSnapshotRecord($auditEventId, 'before', $beforeSnapshot, $occurredAt),
            $this->toAuditSnapshotRecord($auditEventId, 'after', $afterSnapshot, $occurredAt),
        ]);

        $this->changeContext->clear();
    }
}
