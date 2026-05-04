<?php

declare(strict_types=1);

namespace App\Adapters\In\Http\Controllers\Auth;

use App\Adapters\In\Http\Requests\Auth\LoginRequest;
use App\Application\IdentityAccess\Services\LoginActorAccessDecision;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;

final class AuthenticateController extends Controller
{
    public function __construct(
        private readonly LoginActorAccessDecision $actors,
    ) {
    }

    public function __invoke(LoginRequest $request): RedirectResponse
    {
        if (Auth::attempt($request->credentials(), $request->boolean('remember')) === false) {
            return back()
                ->withErrors([
                    'email' => 'Email atau password tidak valid.',
                ])
                ->withInput($request->safe()->only(['email']));
        }

        $request->session()->regenerate();

        $actorId = $request->user()?->getAuthIdentifier();

        if ($actorId === null) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()
                ->route('login')
                ->with('error', 'Autentikasi gagal diproses.');
        }

        $decision = $this->actors->resolve((string) $actorId);

        if ($decision === LoginActorAccessDecision::ADMIN) {
            return redirect()
                ->intended(route('admin.dashboard'))
                ->with('success', 'Login berhasil.');
        }

        if ($decision === LoginActorAccessDecision::KASIR) {
            return redirect()
                ->intended(route('cashier.dashboard'))
                ->with('success', 'Login berhasil.');
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
