<?php

declare(strict_types=1);

namespace App\Application\IdentityAccess\Services;

use App\Application\IdentityAccess\Policies\AdminPageAccessPolicy;
use App\Ports\Out\IdentityAccess\ActorAccessReaderPort;

final readonly class AdminPageRouteAccessDecision
{
    public const ALLOWED = 'allowed';
    public const UNKNOWN = 'unknown';
    public const KASIR_REJECTED = 'kasir_rejected';
    public const DENIED = 'denied';

    public function __construct(
        private ActorAccessReaderPort $actors,
        private AdminPageAccessPolicy $policy,
    ) {
    }

    public function resolve(string $actorId): string
    {
        $actor = $this->actors->findByActorId($actorId);

        if ($actor === null) {
            return self::UNKNOWN;
        }

        $decision = $this->policy->decide($actorId);

        if ($decision->isFailure() === false) {
            return self::ALLOWED;
        }

        if ($actor->isKasir()) {
            return self::KASIR_REJECTED;
        }

        return self::DENIED;
    }
}
