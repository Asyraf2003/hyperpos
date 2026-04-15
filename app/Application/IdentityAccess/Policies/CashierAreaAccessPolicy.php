<?php

declare(strict_types=1);

namespace App\Application\IdentityAccess\Policies;

use App\Application\Shared\DTO\Result;
use App\Ports\Out\AuditLogPort;
use App\Ports\Out\IdentityAccess\ActorAccessReaderPort;
use App\Ports\Out\IdentityAccess\AdminCashierAreaAccessStatePort;

final class CashierAreaAccessPolicy
{
    public function __construct(
        private readonly ActorAccessReaderPort $actors,
        private readonly AdminCashierAreaAccessStatePort $capabilities,
        private readonly AuditLogPort $audit,
    ) {
    }

    /**
     * @param array<string, mixed> $context
     */
    public function decide(string $actorId, array $context = []): Result
    {
        $actor = $this->actors->findByActorId($actorId);

        if ($actor === null) {
            return Result::failure(
                'Aktor tidak dikenali.',
                ['actor' => ['ACTOR_NOT_FOUND']]
            );
        }

        if ($actor->isKasir()) {
            return Result::success(
                [
                    'allowed' => true,
                    'role' => $actor->role()->value(),
                ],
                'Kasir diizinkan mengakses area kasir.'
            );
        }

        if ($actor->isAdmin() === false) {
            return Result::failure(
                'Role tidak diizinkan mengakses area kasir.',
                ['role' => ['CASHIER_AREA_FORBIDDEN']]
            );
        }

        $capability = $this->capabilities->getByActorId($actorId);

        if ($capability->isInactive()) {
            return Result::failure(
                'Admin belum diizinkan mengakses area kasir.',
                ['capability' => ['ADMIN_CASHIER_AREA_ACCESS_DISABLED']]
            );
        }

        $this->audit->record('admin_cashier_area_access_used', [
            'actor_id' => $actorId,
            'role' => $actor->role()->value(),
            'capability' => $capability->capabilityKey(),
            'context' => $context,
        ]);

        return Result::success(
            [
                'allowed' => true,
                'role' => $actor->role()->value(),
                'capability' => $capability->capabilityKey(),
            ],
            'Admin diizinkan mengakses area kasir.'
        );
    }
}
