<?php

declare(strict_types=1);

namespace App\Adapters\In\Http\Middleware\IdentityAccess;

use App\Application\IdentityAccess\Policies\AdminPageAccessPolicy;
use App\Ports\Out\IdentityAccess\ActorAccessReaderPort;
use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

final class EnsureAdminPageAccess
{
    public function __construct(
        private readonly AdminPageAccessPolicy $policy,
        private readonly ActorAccessReaderPort $actors,
    ) {
    }

    /**
     * @param Closure(Request): Response $next
     */
    public function handle(Request $request, Closure $next): Response|RedirectResponse
    {
        $actorId = $request->user()?->getAuthIdentifier();

        if ($actorId === null) {
            return redirect()
                ->route('login')
                ->with('error', 'Autentikasi dibutuhkan.');
        }

        $actor = $this->actors->findByActorId((string) $actorId);

        if ($actor === null) {
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()
                ->route('login')
                ->with('error', 'Aktor tidak dikenali.');
        }

        $decision = $this->policy->decide((string) $actorId);

        if ($decision->isFailure()) {
            if ($actor->isKasir()) {
                return redirect()
                    ->route('cashier.dashboard')
                    ->with('error', 'Halaman admin hanya untuk role admin.');
            }

            return redirect()
                ->route('login')
                ->with('error', 'Akses halaman admin ditolak.');
        }

        return $next($request);
    }
}