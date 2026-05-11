<?php

declare(strict_types=1);

namespace App\Application\MobileApi\Auth\Services;

use App\Application\IdentityAccess\Services\LoginActorAccessDecision;
use App\Application\MobileApi\Auth\DTO\MobileApiActor;
use App\Application\MobileApi\Auth\DTO\MobileApiActorResolution;
use App\Ports\Out\MobileApi\MobileApiUserReaderPort;

final readonly class MobileApiActorResolver
{
    public function __construct(
        private MobileApiUserReaderPort $users,
        private LoginActorAccessDecision $actors,
    ) {
    }

    public function resolve(string $userId): MobileApiActorResolution
    {
        $user = $this->users->findById($userId);

        if ($user === null) {
            return MobileApiActorResolution::missingUser();
        }

        $decision = $this->actors->resolve($user->id);

        if ($decision === LoginActorAccessDecision::ADMIN) {
            return MobileApiActorResolution::resolved(new MobileApiActor(
                id: $user->id,
                name: $user->name,
                email: $user->email,
                role: LoginActorAccessDecision::ADMIN,
            ));
        }

        if ($decision === LoginActorAccessDecision::KASIR) {
            return MobileApiActorResolution::resolved(new MobileApiActor(
                id: $user->id,
                name: $user->name,
                email: $user->email,
                role: LoginActorAccessDecision::KASIR,
            ));
        }

        if ($decision === LoginActorAccessDecision::UNKNOWN) {
            return MobileApiActorResolution::unknown();
        }

        return MobileApiActorResolution::unsupported();
    }
}
