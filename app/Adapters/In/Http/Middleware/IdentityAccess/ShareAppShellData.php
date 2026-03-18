<?php

declare(strict_types=1);

namespace App\Adapters\In\Http\Middleware\IdentityAccess;

use App\Ports\Out\IdentityAccess\ActorAccessReaderPort;
use App\Ports\Out\IdentityAccess\AdminCashierAreaAccessStatePort;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class ShareAppShellData
{
    public function __construct(
        private readonly ActorAccessReaderPort $actors,
        private readonly AdminCashierAreaAccessStatePort $cashierAreaAccessStates,
    ) {
    }

    /**
     * @param Closure(Request): Response $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $appShell = [
            'user_email' => null,
            'actor_label' => null,
            'is_admin_actor' => false,
            'can_access_cashier_area' => false,
        ];

        $user = $request->user();

        if ($user !== null) {
            $actorId = (string) $user->getAuthIdentifier();
            $actor = $this->actors->findByActorId($actorId);

            $appShell['user_email'] = $user->email;

            if ($actor !== null) {
                $appShell['actor_label'] = ucfirst($actor->role()->value());
                $appShell['is_admin_actor'] = $actor->isAdmin();

                if ($actor->isAdmin()) {
                    $capability = $this->cashierAreaAccessStates->getByActorId($actorId);
                    $appShell['can_access_cashier_area'] = $capability->isActive();
                }
            }
        }

        view()->share('appShell', $appShell);

        return $next($request);
    }
}