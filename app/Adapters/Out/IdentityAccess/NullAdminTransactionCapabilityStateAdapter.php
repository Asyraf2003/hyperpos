<?php

declare(strict_types=1);

namespace App\Adapters\Out\IdentityAccess;

use App\Core\IdentityAccess\Capability\AdminTransactionCapabilityState;
use App\Ports\Out\IdentityAccess\AdminTransactionCapabilityStatePort;

final class NullAdminTransactionCapabilityStateAdapter implements AdminTransactionCapabilityStatePort
{
    public function getByActorId(string $actorId): AdminTransactionCapabilityState
    {
        return AdminTransactionCapabilityState::inactive($actorId);
    }

    public function activate(string $actorId): void
    {
        // Intentionally no-op for baseline skeleton.
    }

    public function deactivate(string $actorId): void
    {
        // Intentionally no-op for baseline skeleton.
    }
}
