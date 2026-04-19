<?php

declare(strict_types=1);

namespace App\Adapters\Out\IdentityAccess;

use App\Core\IdentityAccess\Actor\ActorAccess;
use App\Ports\Out\IdentityAccess\ActorAccessReaderPort;

final class NullActorAccessReaderAdapter implements ActorAccessReaderPort
{
    public function findByActorId(string $actorId): ?ActorAccess
    {
        return null;
    }
}
