<?php

declare(strict_types=1);

namespace App\Application\IdentityAccess\Services;

use App\Ports\Out\IdentityAccess\ActorAccessReaderPort;
use App\Ports\Out\IdentityAccess\AdminCashierAreaAccessStatePort;

final readonly class AppShellDataResolver
{
    public function __construct(
        private ActorAccessReaderPort $actors,
        private AdminCashierAreaAccessStatePort $cashierAreaAccessStates,
    ) {
    }

    /**
     * @return array{
     *   user_email:?string,
     *   actor_label:?string,
     *   is_admin_actor:bool,
     *   can_access_cashier_area:bool
     * }
     */
    public function resolve(?string $actorId, ?string $userEmail): array
    {
        $appShell = [
            'user_email' => $userEmail,
            'actor_label' => null,
            'is_admin_actor' => false,
            'can_access_cashier_area' => false,
        ];

        if ($actorId === null) {
            return $appShell;
        }

        $actor = $this->actors->findByActorId($actorId);

        if ($actor === null) {
            return $appShell;
        }

        $appShell['actor_label'] = ucfirst($actor->role()->value());
        $appShell['is_admin_actor'] = $actor->isAdmin();

        if (! $actor->isAdmin()) {
            return $appShell;
        }

        $capability = $this->cashierAreaAccessStates->getByActorId($actorId);
        $appShell['can_access_cashier_area'] = $capability->isActive();

        return $appShell;
    }
}
