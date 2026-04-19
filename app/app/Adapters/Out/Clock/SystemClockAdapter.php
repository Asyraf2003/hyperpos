<?php

declare(strict_types=1);

namespace App\Adapters\Out\Clock;

use App\Ports\Out\ClockPort;
use DateTimeImmutable;

final class SystemClockAdapter implements ClockPort
{
    public function now(): DateTimeImmutable
    {
        return new DateTimeImmutable();
    }
}
