<?php

declare(strict_types=1);

namespace App\Ports\Out;

use DateTimeImmutable;

interface ClockPort
{
    public function now(): DateTimeImmutable;
}
