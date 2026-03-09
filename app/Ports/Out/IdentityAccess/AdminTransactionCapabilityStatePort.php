<?php

declare(strict_types=1);

namespace App\Ports\Out\IdentityAccess;

use App\Core\IdentityAccess\Capability\AdminTransactionCapabilityState;

interface AdminTransactionCapabilityStatePort
{
    public function getByActorId(string $actorId): AdminTransactionCapabilityState;

    public function activate(string $actorId): void;

    public function deactivate(string $actorId): void;
}
