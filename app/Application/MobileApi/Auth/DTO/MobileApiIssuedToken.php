<?php

declare(strict_types=1);

namespace App\Application\MobileApi\Auth\DTO;

use Carbon\CarbonImmutable;

final readonly class MobileApiIssuedToken
{
    public function __construct(
        public string $plainToken,
        public string $tokenType,
        public CarbonImmutable $expiresAt,
    ) {
    }
}
