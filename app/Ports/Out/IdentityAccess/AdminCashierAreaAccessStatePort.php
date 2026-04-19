<?php

declare(strict_types=1);

namespace App\Ports\Out\IdentityAccess;

use App\Core\IdentityAccess\Capability\AdminCashierAreaAccessState;

interface AdminCashierAreaAccessStatePort
{
    public function getByActorId(string $actorId): AdminCashierAreaAccessState;

    public function activate(string $actorId): void;

    public function deactivate(string $actorId): void;
}