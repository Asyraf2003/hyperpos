<?php

declare(strict_types=1);

namespace App\Adapters\Out\EmployeeFinance\Concerns;

trait RecordsEmployeeHistory
{
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
}
