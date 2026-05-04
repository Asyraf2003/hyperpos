<?php

declare(strict_types=1);

namespace App\Adapters\In\Http\Middleware\IdentityAccess;

use App\Application\IdentityAccess\Services\AdminPageRouteAccessDecision;
use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

final class EnsureAdminPageAccess
{
    public function __construct(
        private readonly AdminPageRouteAccessDecision $access,
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

        $decision = $this->access->resolve((string) $actorId);

        if ($decision === AdminPageRouteAccessDecision::UNKNOWN) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()
                ->route('login')
                ->with('error', 'Aktor tidak dikenali.');
        }

        if ($decision === AdminPageRouteAccessDecision::KASIR_REJECTED) {
            return redirect()
                ->route('cashier.dashboard')
                ->with('error', 'Halaman admin hanya untuk role admin.');
        }

        if ($decision === AdminPageRouteAccessDecision::DENIED) {
            return redirect()
                ->route('login')
                ->with('error', 'Akses halaman admin ditolak.');
        }

        return $next($request);
    }
}
