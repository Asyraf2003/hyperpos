<?php

declare(strict_types=1);

namespace App\Application\MobileApi\Auth\DTO;

use Carbon\CarbonImmutable;

final readonly class MobileApiTokenRecord
{
    public function __construct(
        public string $id,
        public string $userId,
        public string $deviceName,
        public CarbonImmutable $expiresAt,
    ) {
    }
}
