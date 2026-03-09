<?php

declare(strict_types=1);

namespace App\Ports\Out;

interface AuditLogPort
{
    /**
     * @param array<string, mixed> $context
     */
    public function record(string $event, array $context = []): void;
}
