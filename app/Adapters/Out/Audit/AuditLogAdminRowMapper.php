<?php

declare(strict_types=1);

namespace App\Adapters\Out\Audit;

final class AuditLogAdminRowMapper
{
    public function __construct(
        private readonly AuditJsonPayload $json = new AuditJsonPayload(),
        private readonly AuditReasonResolver $reasons = new AuditReasonResolver(),
        private readonly AuditLegacyEntityResolver $legacyEntities = new AuditLegacyEntityResolver(),
    ) {
    }

    /**
     * @return array{id:string,source:string,event:string,reason:string,actor_id:?string,actor_role:?string,entity_type:?string,entity_id:?string,bounded_context:?string,context:array<string,mixed>,context_json:string,created_at:string}
     */
    public function map(object $row): array
    {
        return $this->mapLegacy($row);
    }

    /**
     * @return array{id:string,source:string,event:string,reason:string,actor_id:?string,actor_role:?string,entity_type:?string,entity_id:?string,bounded_context:?string,context:array<string,mixed>,context_json:string,created_at:string}
     */
    public function mapLegacy(object $row): array
    {
        $context = $this->json->decode((string) $row->context);

        return [
            'id' => (string) $row->id,
            'source' => 'audit_logs',
            'event' => (string) $row->event,
            'reason' => $this->reasons->fromContext($context),
            'actor_id' => $this->nullableScalar($context['actor_id'] ?? null),
            'actor_role' => $this->nullableScalar($context['actor_role'] ?? null),
            'entity_type' => null,
            'entity_id' => $this->legacyEntities->resolve($context),
            'bounded_context' => null,
            'context' => $context,
            'context_json' => $this->json->encodePretty($context),
            'created_at' => (string) $row->created_at,
        ];
    }

    /**
     * @return array{id:string,source:string,event:string,reason:string,actor_id:?string,actor_role:?string,entity_type:?string,entity_id:?string,bounded_context:?string,context:array<string,mixed>,context_json:string,created_at:string}
     */
    public function mapEvent(object $row): array
    {
        $metadata = $this->json->decode($row->metadata_json !== null ? (string) $row->metadata_json : null);
        $context = $this->eventContext($row, $metadata);

        return [
            'id' => (string) $row->id,
            'source' => 'audit_events',
            'event' => (string) $row->event_name,
            'reason' => $this->reasons->fromScalar($row->reason),
            'actor_id' => $row->actor_id !== null ? (string) $row->actor_id : null,
            'actor_role' => $row->actor_role !== null ? (string) $row->actor_role : null,
            'entity_type' => (string) $row->aggregate_type,
            'entity_id' => (string) $row->aggregate_id,
            'bounded_context' => (string) $row->bounded_context,
            'context' => $context,
            'context_json' => $this->json->encodePretty($context),
            'created_at' => (string) $row->occurred_at,
        ];
    }

    /**
     * @param array<string, mixed> $metadata
     * @return array<string, mixed>
     */
    private function eventContext(object $row, array $metadata): array
    {
        return [
            'audit_event' => [
                'bounded_context' => (string) $row->bounded_context,
                'aggregate_type' => (string) $row->aggregate_type,
                'aggregate_id' => (string) $row->aggregate_id,
                'actor_id' => $row->actor_id !== null ? (string) $row->actor_id : null,
                'actor_role' => $row->actor_role !== null ? (string) $row->actor_role : null,
                'source_channel' => $row->source_channel !== null ? (string) $row->source_channel : null,
            ],
            'metadata' => $metadata,
        ];
    }

    private function nullableScalar(mixed $value): ?string
    {
        if (! is_scalar($value)) {
            return null;
        }

        $trimmed = trim((string) $value);

        return $trimmed === '' ? null : $trimmed;
    }
}
