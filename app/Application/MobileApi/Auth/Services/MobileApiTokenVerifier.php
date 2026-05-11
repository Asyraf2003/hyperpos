<?php

declare(strict_types=1);

namespace App\Application\MobileApi\Auth\Services;

use App\Application\MobileApi\Auth\DTO\MobileApiTokenRecord;
use App\Ports\Out\MobileApi\MobileApiTokenStorePort;
use Carbon\CarbonImmutable;

final readonly class MobileApiTokenVerifier
{
    public function __construct(
        private MobileApiTokenStorePort $tokens,
        private MobileApiTokenHasher $hasher,
    ) {
    }

    public function verify(?string $plainToken): ?MobileApiTokenRecord
    {
        $token = trim((string) $plainToken);

        if ($token === '') {
            return null;
        }

        return $this->tokens->findActiveByTokenHash(
            $this->hasher->hash($token),
            CarbonImmutable::now(),
        );
    }
}
