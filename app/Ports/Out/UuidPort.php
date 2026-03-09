<?php

declare(strict_types=1);

namespace App\Ports\Out;

interface UuidPort
{
    public function generate(): string;
}
