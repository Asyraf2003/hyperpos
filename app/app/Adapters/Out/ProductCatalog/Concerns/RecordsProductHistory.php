<?php

declare(strict_types=1);

namespace App\Adapters\Out\ProductCatalog\Concerns;

use DateTimeInterface;
use Illuminate\Support\Facades\DB;

trait RecordsProductHistory
{
    /**
     * @param array<string, mixed> $snapshot
     */
    private function recordProductVersion(
        string $productId,
        int $revisionNo,
        string $eventName,
        DateTimeInterface $occurredAt,
        ?string $actorId,
        ?string $reason,
        array $snapshot,
    ): void {
        DB::table('product_versions')->insert([
            'id' => $this->uuid->generate(),
            'product_id' => $productId,
            'revision_no' => $revisionNo,
            'event_name' => $eventName,
            'changed_at' => $occurredAt,
            'changed_by_actor_id' => $actorId,
            'change_reason' => $reason,
            'snapshot_json' => json_encode($snapshot, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE),
        ]);
    }

    /**
     * @param array<string, mixed> $snapshot
     */
    private function recordProductAuditEvent(
        string $productId,
        int $revisionNo,
        string $eventName,
        DateTimeInterface $occurredAt,
        ?string $actorId,
        ?string $actorRole,
        ?string $reason,
        ?string $sourceChannel,
        array $snapshot,
    ): void {
        DB::table('audit_events')->insert([
            'id' => $this->uuid->generate(),
            'bounded_context' => 'product_catalog',
            'aggregate_type' => 'product',
            'aggregate_id' => $productId,
            'event_name' => $eventName,
            'occurred_at' => $occurredAt,
            'actor_id' => $actorId,
            'actor_role' => $actorRole,
            'reason' => $reason,
            'source_channel' => $sourceChannel,
            'request_id' => null,
            'correlation_id' => null,
            'metadata_json' => json_encode([
                'product' => $snapshot,
                'revision_no' => $revisionNo,
            ], JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE),
        ]);
    }
}
