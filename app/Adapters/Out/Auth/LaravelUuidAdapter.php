<?php

declare(strict_types=1);

namespace App\Adapters\Out\Auth;

use App\Ports\Out\UuidPort;
use Illuminate\Support\Str;

final class LaravelUuidAdapter implements UuidPort
{
    public function generate(): string
    {
        return (string) Str::uuid();
    }
}
