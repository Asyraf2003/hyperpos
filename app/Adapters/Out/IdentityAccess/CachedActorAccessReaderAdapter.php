<?php

declare(strict_types=1);

namespace App\Adapters\Out\IdentityAccess;

use App\Application\IdentityAccess\Request\IdentityAccessRequestStore;
use App\Core\IdentityAccess\Actor\ActorAccess;
use App\Ports\Out\IdentityAccess\ActorAccessReaderPort;

final class CachedActorAccessReaderAdapter implements ActorAccessReaderPort
{
    public function __construct(
        private readonly IdentityAccessRequestStore $store,
        private readonly DatabaseActorAccessReaderAdapter $database,
    ) {
    }

    public function findByActorId(string $actorId): ?ActorAccess
    {
        if ($this->store->hasActor($actorId)) {
            return $this->store->actor($actorId);
        }

        return $this->store->rememberActor(
            $actorId,
            $this->database->findByActorId($actorId),
        );
    }
}
