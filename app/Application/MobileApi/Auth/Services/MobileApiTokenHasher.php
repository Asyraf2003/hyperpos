<?php

declare(strict_types=1);

namespace App\Application\MobileApi\Auth\Services;

final class MobileApiTokenHasher
{
    public function hash(string $plainToken): string
    {
        return hash('sha256', $plainToken);
    }
}
