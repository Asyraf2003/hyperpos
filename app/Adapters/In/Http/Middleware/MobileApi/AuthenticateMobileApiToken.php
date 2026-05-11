<?php

declare(strict_types=1);

namespace App\Adapters\In\Http\Middleware\MobileApi;

use App\Application\MobileApi\Auth\Services\MobileApiActorResolver;
use App\Application\MobileApi\Auth\Services\MobileApiTokenVerifier;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final readonly class AuthenticateMobileApiToken
{
    public function __construct(
        private MobileApiTokenVerifier $tokens,
        private MobileApiActorResolver $actors,
    ) {
    }

    /**
     * @param Closure(Request): Response $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $token = $this->tokens->verify($request->bearerToken());

        if ($token === null) {
            return $this->unauthenticated();
        }

        $actor = $this->actors->resolve($token->userId);

        if (!$actor->isResolved() || $actor->actor === null) {
            return $this->unauthenticated();
        }

        $request->attributes->set('mobile_api_token_id', $token->id);
        $request->attributes->set('mobile_api_actor', $actor->actor);

        return $next($request);
    }

    private function unauthenticated(): JsonResponse
    {
        return response()->json([
            'success' => false,
            'data' => null,
            'message' => 'Autentikasi diperlukan.',
            'errors' => [
                'token' => ['UNAUTHENTICATED'],
            ],
        ], 401);
    }
}
