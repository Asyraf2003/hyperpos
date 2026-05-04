<?php

declare(strict_types=1);

namespace App\Application\IdentityAccess\Services;

use App\Application\IdentityAccess\Policies\CashierAreaAccessPolicy;
use App\Ports\Out\IdentityAccess\ActorAccessReaderPort;

final readonly class CashierAreaRouteAccessDecision
{
    public const ALLOWED = 'allowed';
    public const UNKNOWN = 'unknown';
    public const ADMIN_REJECTED = 'admin_rejected';
    public const DENIED = 'denied';

    public function __construct(
        private ActorAccessReaderPort $actors,
        private CashierAreaAccessPolicy $policy,
    ) {
    }

    /**
     * @param array<string, mixed> $context
     */
    public function resolve(string $actorId, array $context = []): string
    {
        $actor = $this->actors->findByActorId($actorId);

        if ($actor === null) {
            return self::UNKNOWN;
        }

        $decision = $this->policy->decide($actorId, $context);

        if ($decision->isFailure() === false) {
            return self::ALLOWED;
        }

        if ($actor->isAdmin()) {
            return self::ADMIN_REJECTED;
        }

        return self::DENIED;
    }
}
