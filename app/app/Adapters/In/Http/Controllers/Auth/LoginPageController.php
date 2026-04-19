<?php

declare(strict_types=1);

namespace App\Adapters\In\Http\Controllers\Auth;

use App\Ports\Out\IdentityAccess\ActorAccessReaderPort;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;

final class LoginPageController extends Controller
{
    public function __construct(
        private readonly ActorAccessReaderPort $actors,
    ) {
    }

    public function __invoke(Request $request): View|RedirectResponse
    {
        $actorId = $request->user()?->getAuthIdentifier();

        if ($actorId === null) {
            return view('auth.login');
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

        if ($actor->isAdmin()) {
            return redirect()->route('admin.dashboard');
        }

        if ($actor->isKasir()) {
            return redirect()->route('cashier.dashboard');
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()
            ->route('login')
            ->with('error', 'Role aktor tidak didukung.');
    }
}