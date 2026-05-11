<?php

declare(strict_types=1);

namespace App\Application\MobileApi\Auth\Services;

use App\Application\MobileApi\Auth\DTO\MobileApiIssuedToken;
use App\Ports\Out\MobileApi\MobileApiTokenStorePort;
use Carbon\CarbonImmutable;

final readonly class MobileApiTokenIssuer
{
    public function __construct(
        private MobileApiTokenStorePort $tokens,
        private MobileApiTokenHasher $hasher,
    ) {
    }

    public function issue(string $userId, string $deviceName): MobileApiIssuedToken
    {
        $now = CarbonImmutable::now();
        $expiresAt = $now->addDays(30);
        $plainToken = bin2hex(random_bytes(32));

        $this->tokens->create(
            userId: $userId,
            tokenHash: $this->hasher->hash($plainToken),
            deviceName: trim($deviceName),
            expiresAt: $expiresAt,
            now: $now,
        );

        return new MobileApiIssuedToken(
            plainToken: $plainToken,
            tokenType: 'Bearer',
            expiresAt: $expiresAt,
        );
    }
}
