<?php

declare(strict_types=1);

namespace App\Adapters\In\Http\Controllers\Auth;

use App\Application\IdentityAccess\Services\LoginActorAccessDecision;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;

final class LoginPageController extends Controller
{
    public function __construct(
        private readonly LoginActorAccessDecision $actors,
    ) {
    }

    public function __invoke(Request $request): View|RedirectResponse
    {
        $actorId = $request->user()?->getAuthIdentifier();

        if ($actorId === null) {
            return view('auth.login');
        }

        $decision = $this->actors->resolve((string) $actorId);

        if ($decision === LoginActorAccessDecision::ADMIN) {
            return redirect()->route('admin.dashboard');
        }

        if ($decision === LoginActorAccessDecision::KASIR) {
            return redirect()->route('cashier.dashboard');
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()
            ->route('login')
            ->with('error', $decision === LoginActorAccessDecision::UNKNOWN
                ? 'Aktor tidak dikenali.'
                : 'Role aktor tidak didukung.');
    }
}
