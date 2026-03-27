<?php

declare(strict_types=1);

namespace App\Adapters\In\Http\Middleware\IdentityAccess;

use App\Ports\Out\IdentityAccess\ActorAccessReaderPort;
use App\Ports\Out\IdentityAccess\AdminCashierAreaAccessStatePort;
use Illuminate\Http\Request;

final class AppShellDataBuilder
{
    public function __construct(
        private readonly ActorAccessReaderPort $actors,
        private readonly AdminCashierAreaAccessStatePort $cashierAreaAccessStates,
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
    public function build(Request $request): array
    {
        $appShell = [
            'user_email' => null,
            'actor_label' => null,
            'is_admin_actor' => false,
            'can_access_cashier_area' => false,
        ];

        $user = $request->user();

        if ($user === null) {
            return $appShell;
        }

        $actorId = (string) $user->getAuthIdentifier();
        $actor = $this->actors->findByActorId($actorId);

        $appShell['user_email'] = $user->email;

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
