<?php

declare(strict_types=1);

namespace App\Adapters\Out\IdentityAccess;

use App\Application\IdentityAccess\Request\IdentityAccessRequestStore;
use App\Core\IdentityAccess\Capability\AdminTransactionCapabilityState;
use App\Ports\Out\IdentityAccess\AdminTransactionCapabilityStatePort;

final class CachedAdminTransactionCapabilityStateAdapter implements AdminTransactionCapabilityStatePort
{
    public function __construct(
        private readonly IdentityAccessRequestStore $store,
        private readonly DatabaseAdminTransactionCapabilityStateAdapter $database,
    ) {
    }

    public function getByActorId(string $actorId): AdminTransactionCapabilityState
    {
        if ($this->store->hasTransactionCapability($actorId)) {
            return $this->store->transactionCapability($actorId);
        }

        return $this->store->rememberTransactionCapability(
            $actorId,
            $this->database->getByActorId($actorId),
        );
    }

    public function activate(string $actorId): void
    {
        $this->database->activate($actorId);
        $this->store->rememberTransactionCapability(
            $actorId,
            AdminTransactionCapabilityState::active($actorId),
        );
    }

    public function deactivate(string $actorId): void
    {
        $this->database->deactivate($actorId);
        $this->store->rememberTransactionCapability(
            $actorId,
            AdminTransactionCapabilityState::inactive($actorId),
        );
    }
}
