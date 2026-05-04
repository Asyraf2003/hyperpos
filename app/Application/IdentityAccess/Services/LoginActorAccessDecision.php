<?php

declare(strict_types=1);

namespace App\Application\IdentityAccess\Services;

use App\Ports\Out\IdentityAccess\ActorAccessReaderPort;

final readonly class LoginActorAccessDecision
{
    public const UNKNOWN = 'unknown';
    public const ADMIN = 'admin';
    public const KASIR = 'kasir';
    public const UNSUPPORTED = 'unsupported';

    public function __construct(private ActorAccessReaderPort $actors)
    {
    }

    public function resolve(string $actorId): string
    {
        $actor = $this->actors->findByActorId($actorId);

        if ($actor === null) {
            return self::UNKNOWN;
        }

        if ($actor->isAdmin()) {
            return self::ADMIN;
        }

        if ($actor->isKasir()) {
            return self::KASIR;
        }

        return self::UNSUPPORTED;
    }
}
