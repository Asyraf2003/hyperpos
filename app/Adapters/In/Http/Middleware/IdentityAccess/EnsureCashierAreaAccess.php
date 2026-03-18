<?php

declare(strict_types=1);

namespace App\Adapters\In\Http\Middleware\IdentityAccess;

use App\Application\IdentityAccess\Policies\CashierAreaAccessPolicy;
use App\Ports\Out\IdentityAccess\ActorAccessReaderPort;
use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

final class EnsureCashierAreaAccess
{
    public function __construct(
        private readonly CashierAreaAccessPolicy $policy,
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
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()
                ->route('login')
                ->with('error', 'Aktor tidak dikenali.');
        }

        $decision = $this->policy->decide((string) $actorId, [
            'path' => $request->path(),
            'route_name' => $request->route()->getName(),
        ]);

        if ($decision->isFailure()) {
            if ($actor->isAdmin()) {
                return redirect()
                    ->route('admin.dashboard')
                    ->with('error', 'Admin belum diizinkan mengakses area kasir.');
            }

            return redirect()
                ->route('login')
                ->with('error', 'Akses area kasir ditolak.');
        }

        return $next($request);
    }
}