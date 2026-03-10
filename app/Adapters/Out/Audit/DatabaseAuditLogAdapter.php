<?php

declare(strict_types=1);

namespace App\Adapters\Out\Audit;

use App\Ports\Out\AuditLogPort;
use Illuminate\Support\Facades\DB;

final class DatabaseAuditLogAdapter implements AuditLogPort
{
    /**
     * @param array<string, mixed> $context
     */
    public function record(string $event, array $context = []): void
    {
        DB::table('audit_logs')->insert([
            'event' => $event,
            'context' => json_encode($context, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE),
        ]);
    }
}
