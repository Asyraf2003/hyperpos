<?php

declare(strict_types=1);

namespace App\Adapters\Out\IdentityAccess;

use App\Application\IdentityAccess\Request\IdentityAccessRequestStore;
use App\Core\IdentityAccess\Capability\AdminCashierAreaAccessState;
use App\Ports\Out\IdentityAccess\AdminCashierAreaAccessStatePort;

final class CachedAdminCashierAreaAccessStateAdapter implements AdminCashierAreaAccessStatePort
{
    public function __construct(
        private readonly IdentityAccessRequestStore $store,
        private readonly DatabaseAdminCashierAreaAccessStateAdapter $database,
    ) {
    }

    public function getByActorId(string $actorId): AdminCashierAreaAccessState
    {
        if ($this->store->hasCashierAreaAccess($actorId)) {
            return $this->store->cashierAreaAccess($actorId);
        }

        return $this->store->rememberCashierAreaAccess(
            $actorId,
            $this->database->getByActorId($actorId),
        );
    }

    public function activate(string $actorId): void
    {
        $this->database->activate($actorId);
        $this->store->rememberCashierAreaAccess(
            $actorId,
            AdminCashierAreaAccessState::active($actorId),
        );
    }

    public function deactivate(string $actorId): void
    {
        $this->database->deactivate($actorId);
        $this->store->rememberCashierAreaAccess(
            $actorId,
            AdminCashierAreaAccessState::inactive($actorId),
        );
    }
}
