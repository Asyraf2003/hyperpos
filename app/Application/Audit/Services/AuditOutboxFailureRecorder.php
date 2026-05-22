<?php

declare(strict_types=1);

namespace App\Application\Audit\Services;

use App\Application\Audit\Support\AuditOutboxStatus;
use App\Ports\Out\ClockPort;
use Illuminate\Support\Facades\DB;
use Throwable;

final class AuditOutboxFailureRecorder
{
    public function __construct(
        private readonly ClockPort $clock,
    ) {
    }

    public function record(string $rowId, Throwable $e, int $maxAttempts): void
    {
        $row = DB::table('audit_outbox')->where('id', $rowId)->first();

        if ($row === null) {
            return;
        }

        $attempts = ((int) $row->attempts) + 1;
        $status = $attempts >= $maxAttempts
            ? AuditOutboxStatus::FAILED
            : AuditOutboxStatus::PENDING;

        DB::table('audit_outbox')
            ->where('id', $rowId)
            ->update([
                'status' => $status,
                'attempts' => $attempts,
                'last_error' => substr($e->getMessage(), 0, 1000),
                'locked_at' => null,
                'updated_at' => $this->clock->now(),
            ]);
    }
}
