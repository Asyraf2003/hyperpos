<?php

declare(strict_types=1);

namespace App\Adapters\Out\EmployeeFinance;

use App\Application\EmployeeFinance\Context\EmployeeChangeContext;
use App\Core\EmployeeFinance\Employee\Employee;
use App\Ports\Out\ClockPort;
use App\Ports\Out\EmployeeFinance\EmployeeWriterPort;
use App\Ports\Out\UuidPort;
use Illuminate\Support\Facades\DB;

final class DatabaseVersionedEmployeeWriterAdapter implements EmployeeWriterPort
{
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

    private function persistCreatedEmployee(Employee $employee): void
    {
        $revisionNo = 1;
        $occurredAt = $this->clock->now();
        $context = $this->changeContext->snapshot();
        $snapshot = $this->toSnapshot($employee);
        $eventName = 'employee_created';
        $auditEventId = $this->uuid->generate();

        DB::table('employees')->insert($this->toEmployeeRecord($employee));
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
            ->update($this->toEmployeeRecord($employee));

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

    private function nextRevisionNo(string $employeeId): int
    {
        $lastRevision = DB::table('employee_versions')
            ->where('employee_id', $employeeId)
            ->max('revision_no');

        return ((int) $lastRevision) + 1;
    }

    /**
     * @return array<string, string|int|null>
     */
    private function toEmployeeRecord(Employee $employee): array
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
            'updated_at' => $occurredAt = $this->clock->now(),
            'created_at' => $occurredAt,
        ];
    }

    /**
     * @param array{actor_id:?string,actor_role:?string,source_channel:?string,reason:?string} $context
     * @param array<string,mixed> $snapshot
     * @return array<string,mixed>
     */
    private function toVersionRecord(
        string $employeeId,
        int $revisionNo,
        string $eventName,
        \DateTimeImmutable $occurredAt,
        array $context,
        array $snapshot,
    ): array {
        return [
            'id' => $this->uuid->generate(),
            'employee_id' => $employeeId,
            'revision_no' => $revisionNo,
            'event_name' => $eventName,
            'changed_by_actor_id' => $context['actor_id'],
            'change_reason' => $context['reason'],
            'changed_at' => $occurredAt,
            'snapshot_json' => json_encode($snapshot, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE),
        ];
    }

    /**
     * @param array{actor_id:?string,actor_role:?string,source_channel:?string,reason:?string} $context
     * @param array<string,mixed> $snapshot
     * @return array<string,mixed>
     */
    private function toAuditEventRecord(
        string $auditEventId,
        string $employeeId,
        int $revisionNo,
        string $eventName,
        \DateTimeImmutable $occurredAt,
        array $context,
        array $snapshot,
    ): array {
        return [
            'id' => $auditEventId,
            'bounded_context' => 'employee_finance',
            'aggregate_type' => 'employee',
            'aggregate_id' => $employeeId,
            'event_name' => $eventName,
            'actor_id' => $context['actor_id'],
            'actor_role' => $context['actor_role'],
            'reason' => $context['reason'],
            'source_channel' => $context['source_channel'],
            'request_id' => null,
            'correlation_id' => null,
            'occurred_at' => $occurredAt,
            'metadata_json' => json_encode([
                'employee' => $snapshot,
                'revision_no' => $revisionNo,
            ], JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE),
        ];
    }

    /**
     * @param array<string,mixed> $snapshot
     * @return array<string,mixed>
     */
    private function toAuditSnapshotRecord(
        string $auditEventId,
        string $snapshotKind,
        array $snapshot,
        \DateTimeImmutable $occurredAt,
    ): array {
        return [
            'id' => $this->uuid->generate(),
            'audit_event_id' => $auditEventId,
            'snapshot_kind' => $snapshotKind,
            'payload_json' => json_encode($snapshot, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE),
            'created_at' => $occurredAt,
        ];
    }

    /**
     * @return array{
     *   employee_name:string,
     *   phone:?string,
     *   salary_basis_type:string,
     *   default_salary_amount:?int,
     *   employment_status:string,
     *   started_at:?string,
     *   ended_at:?string
     * }
     */
    private function toSnapshot(Employee $employee): array
    {
        return [
            'employee_name' => $employee->getEmployeeName(),
            'phone' => $employee->getPhone(),
            'salary_basis_type' => $employee->getSalaryBasisType()->value,
            'default_salary_amount' => $employee->getDefaultSalaryAmount()?->amount(),
            'employment_status' => $employee->getEmploymentStatus()->value,
            'started_at' => $employee->getStartedAt()?->format('Y-m-d'),
            'ended_at' => $employee->getEndedAt()?->format('Y-m-d'),
        ];
    }

    /**
     * @return array{
     *   employee_name:string,
     *   phone:?string,
     *   salary_basis_type:string,
     *   default_salary_amount:?int,
     *   employment_status:string,
     *   started_at:?string,
     *   ended_at:?string
     * }
     */
    private function snapshotFromRow(object $row): array
    {
        return [
            'employee_name' => (string) $row->employee_name,
            'phone' => $row->phone !== null ? (string) $row->phone : null,
            'salary_basis_type' => (string) $row->salary_basis_type,
            'default_salary_amount' => $row->default_salary_amount !== null ? (int) $row->default_salary_amount : null,
            'employment_status' => (string) $row->employment_status,
            'started_at' => $row->started_at !== null ? (string) $row->started_at : null,
            'ended_at' => $row->ended_at !== null ? (string) $row->ended_at : null,
        ];
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
