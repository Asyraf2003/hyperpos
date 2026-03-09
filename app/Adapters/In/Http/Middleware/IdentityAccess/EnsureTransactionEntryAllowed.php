<?php

declare(strict_types=1);

namespace App\Adapters\In\Http\Middleware\IdentityAccess;

use App\Adapters\In\Http\Presenters\Response\JsonResultResponder;
use App\Application\IdentityAccess\Policies\TransactionEntryPolicy;
use App\Application\Shared\DTO\Result;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class EnsureTransactionEntryAllowed
{
    public function __construct(
        private readonly TransactionEntryPolicy $policy,
        private readonly JsonResultResponder $responder,
    ) {
    }

    /**
     * @param Closure(Request): Response $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $actorId = $request->user()?->getAuthIdentifier();

        if ($actorId === null) {
            return $this->responder->failure(
                Result::failure(
                    'Autentikasi dibutuhkan.',
                    ['auth' => ['UNAUTHENTICATED']]
                ),
                401
            );
        }

        $decision = $this->policy->decide((string) $actorId);

        if ($decision->isFailure()) {
            return $this->responder->failure($decision, 403);
        }

        return $next($request);
    }
}
