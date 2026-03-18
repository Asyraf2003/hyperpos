<?php

declare(strict_types=1);

namespace App\Application\IdentityAccess\Policies;

use App\Application\Shared\DTO\Result;
use App\Ports\Out\IdentityAccess\ActorAccessReaderPort;

final class AdminPageAccessPolicy
{
    public function __construct(
        private readonly ActorAccessReaderPort $actors,
    ) {
    }

    public function decide(string $actorId): Result
    {
        $actor = $this->actors->findByActorId($actorId);

        if ($actor === null) {
            return Result::failure(
                'Aktor tidak dikenali.',
                ['actor' => ['ACTOR_NOT_FOUND']]
            );
        }

        if ($actor->isAdmin()) {
            return Result::success(
                [
                    'allowed' => true,
                    'role' => $actor->role()->value(),
                ],
                'Admin diizinkan mengakses halaman admin.'
            );
        }

        return Result::failure(
            'Halaman admin hanya untuk role admin.',
            ['role' => ['ADMIN_PAGE_FORBIDDEN']]
        );
    }
}