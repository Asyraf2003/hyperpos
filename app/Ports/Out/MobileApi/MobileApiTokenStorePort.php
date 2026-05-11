<?php

declare(strict_types=1);

namespace App\Ports\Out\MobileApi;

use App\Application\MobileApi\Auth\DTO\MobileApiTokenRecord;
use Carbon\CarbonImmutable;

interface MobileApiTokenStorePort
{
    public function create(
        string $userId,
        string $tokenHash,
        string $deviceName,
        CarbonImmutable $expiresAt,
        CarbonImmutable $now,
    ): MobileApiTokenRecord;

    public function findActiveByTokenHash(
        string $tokenHash,
        CarbonImmutable $now,
    ): ?MobileApiTokenRecord;

    public function revokeById(string $tokenId, CarbonImmutable $now): void;
}
