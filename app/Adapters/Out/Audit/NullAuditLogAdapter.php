<?php

declare(strict_types=1);

namespace App\Adapters\Out\Audit;

use App\Ports\Out\AuditLogPort;

final class NullAuditLogAdapter implements AuditLogPort
{
    /**
     * @param array<string, mixed> $context
     */
    public function record(string $event, array $context = []): void
    {
        // Intentionally no-op for baseline skeleton.
    }
}
