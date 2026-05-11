<?php

declare(strict_types=1);

namespace App\Application\MobileApi\Auth\UseCases;

use App\Application\MobileApi\Auth\DTO\MobileApiLoginResult;
use App\Application\MobileApi\Auth\Services\MobileApiActorResolver;
use App\Application\MobileApi\Auth\Services\MobileApiTokenIssuer;
use App\Ports\Out\MobileApi\MobileApiCredentialVerifierPort;

final readonly class LoginMobileApiUserHandler
{
    public function __construct(
        private MobileApiCredentialVerifierPort $credentials,
        private MobileApiActorResolver $actors,
        private MobileApiTokenIssuer $tokens,
    ) {
    }

    public function handle(string $email, string $password, string $deviceName): MobileApiLoginResult
    {
        $user = $this->credentials->verify($email, $password);

        if ($user === null) {
            return MobileApiLoginResult::invalidCredentials();
        }

        $actor = $this->actors->resolve($user->id);

        if ($actor->status === 'unknown' || $actor->status === 'missing_user') {
            return MobileApiLoginResult::actorUnknown();
        }

        if (!$actor->isResolved() || $actor->actor === null) {
            return MobileApiLoginResult::actorUnsupported();
        }

        return MobileApiLoginResult::success(
            $this->tokens->issue($user->id, $deviceName),
            $actor->actor,
        );
    }
}
