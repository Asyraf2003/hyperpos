<?php

declare(strict_types=1);

namespace Tests\Feature\AuditLog\Support;

use App\Application\Audit\DTO\AuditEventSnapshotWrite;
use App\Application\Audit\DTO\AuditEventWrite;
use DateTimeImmutable;

final class AuditOutboxTestEventFactory
{
    public static function event(string $auditEventId): AuditEventWrite
    {
        return new AuditEventWrite(
            id: $auditEventId,
            boundedContext: 'expense',
            aggregateType: 'expense_category',
            aggregateId: 'cat-1',
            eventName: 'expense_category_updated',
            actorId: 'admin-1',
            actorRole: null,
            reason: null,
            sourceChannel: 'web_admin',
            requestId: 'request-1',
            correlationId: 'correlation-1',
            occurredAt: new DateTimeImmutable('2026-05-23 10:00:00'),
            metadata: [
                'category_id' => 'cat-1',
                'performed_by_actor_id' => 'admin-1',
            ],
            snapshots: [
                new AuditEventSnapshotWrite('before', [
                    'id' => 'cat-1',
                    'code' => 'EXP-ELEC',
                    'name' => 'Listrik',
                    'description' => null,
                    'is_active' => false,
                ]),
                new AuditEventSnapshotWrite('after', [
                    'id' => 'cat-1',
                    'code' => 'EXP-UTIL',
                    'name' => 'Utilitas',
                    'description' => null,
                    'is_active' => true,
                ]),
            ],
        );
    }
}
