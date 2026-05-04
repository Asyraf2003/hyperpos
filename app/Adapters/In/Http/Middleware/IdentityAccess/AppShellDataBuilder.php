<?php

declare(strict_types=1);

namespace App\Adapters\In\Http\Middleware\IdentityAccess;

use App\Application\IdentityAccess\Services\AppShellDataResolver;
use Illuminate\Http\Request;

final class AppShellDataBuilder
{
    public function __construct(
        private readonly AppShellDataResolver $appShells,
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
        $user = $request->user();

        if ($user === null) {
            return $this->appShells->resolve(null, null);
        }

        $userEmail = is_string($user->email) ? $user->email : null;

        return $this->appShells->resolve(
            (string) $user->getAuthIdentifier(),
            $userEmail,
        );
    }
}
