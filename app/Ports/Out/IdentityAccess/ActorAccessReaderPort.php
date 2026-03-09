<?php

declare(strict_types=1);

namespace App\Ports\Out\IdentityAccess;

use App\Core\IdentityAccess\Actor\ActorAccess;

interface ActorAccessReaderPort
{
    public function findByActorId(string $actorId): ?ActorAccess;
}
