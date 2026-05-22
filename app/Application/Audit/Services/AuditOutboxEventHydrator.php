<?php

declare(strict_types=1);

namespace App\Application\Audit\Services;

use App\Application\Audit\DTO\AuditEventSnapshotWrite;
use App\Application\Audit\DTO\AuditEventWrite;
use DateTimeImmutable;
use RuntimeException;

final class AuditOutboxEventHydrator
{
    public function fromRow(object $row): AuditEventWrite
    {
        return new AuditEventWrite(
            id: (string) $row->audit_event_id,
            boundedContext: (string) $row->bounded_context,
            aggregateType: (string) $row->aggregate_type,
            aggregateId: (string) $row->aggregate_id,
            eventName: (string) $row->event_name,
            actorId: $this->nullableString($row->actor_id),
            actorRole: $this->nullableString($row->actor_role),
            reason: $this->nullableString($row->reason),
            sourceChannel: $this->nullableString($row->source_channel),
            requestId: $this->nullableString($row->request_id),
            correlationId: $this->nullableString($row->correlation_id),
            occurredAt: new DateTimeImmutable((string) $row->occurred_at),
            metadata: $this->jsonArray($row->metadata_json),
            snapshots: $this->snapshots($row->snapshots_json),
        );
    }

    private function nullableString(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $trimmed = trim((string) $value);

        return $trimmed === '' ? null : $trimmed;
    }

    private function jsonArray(mixed $json): array
    {
        if ($json === null || trim((string) $json) === '') {
            return [];
        }

        $decoded = json_decode((string) $json, true, 512, JSON_THROW_ON_ERROR);

        if (! is_array($decoded)) {
            throw new RuntimeException('audit_outbox JSON column must decode to an array.');
        }

        return $decoded;
    }

    private function snapshots(mixed $json): array
    {
        $decoded = $this->jsonArray($json);
        $snapshots = [];

        foreach ($decoded as $snapshot) {
            if (! is_array($snapshot)) {
                throw new RuntimeException('audit_outbox snapshot entry must be an array.');
            }

            $payload = $snapshot['payload'] ?? null;

            if (! is_array($payload)) {
                throw new RuntimeException('audit_outbox snapshot payload must be an array.');
            }

            $snapshots[] = new AuditEventSnapshotWrite(
                (string) ($snapshot['snapshot_kind'] ?? ''),
                $payload,
            );
        }

        return $snapshots;
    }
}
