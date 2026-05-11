<?php

declare(strict_types=1);

namespace App\Application\MobileApi\Auth\UseCases;

use App\Ports\Out\MobileApi\MobileApiTokenStorePort;
use Carbon\CarbonImmutable;

final readonly class LogoutMobileApiTokenHandler
{
    public function __construct(private MobileApiTokenStorePort $tokens)
    {
    }

    public function handle(string $tokenId): void
    {
        $this->tokens->revokeById($tokenId, CarbonImmutable::now());
    }
}
