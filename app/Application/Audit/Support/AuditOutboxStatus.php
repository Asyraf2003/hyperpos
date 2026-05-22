<?php

declare(strict_types=1);

namespace App\Application\Audit\Support;

final class AuditOutboxStatus
{
    public const PENDING = 'pending';
    public const PROCESSING = 'processing';
    public const PROCESSED = 'processed';
    public const FAILED = 'failed';
}
