<?php

declare(strict_types=1);

namespace App\Application\IdentityAccess\Policies;

use App\Application\Shared\DTO\Result;
use App\Core\IdentityAccess\Score\TransactionEntryScore;
use App\Ports\Out\AuditLogPort;
use App\Ports\Out\IdentityAccess\ActorAccessReaderPort;
use App\Ports\Out\IdentityAccess\AdminTransactionCapabilityStatePort;

final class TransactionEntryPolicy
{
    public function __construct(
        private readonly ActorAccessReaderPort $actors,
        private readonly AdminTransactionCapabilityStatePort $capabilities,
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
                    'score' => TransactionEntryScore::KASIR_DEFAULT_ALLOW,
                ],
                'Kasir diizinkan input transaksi.'
            );
        }

        if ($actor->isAdmin() === false) {
            return Result::failure(
                'Role tidak diizinkan input transaksi.',
                ['role' => ['AUTH_FORBIDDEN']]
            );
        }

        $capability = $this->capabilities->getByActorId($actorId);

        if ($capability->isInactive()) {
            return Result::failure(
                'Admin belum diizinkan input transaksi.',
                [
                    'capability' => ['ADMIN_TRANSACTION_CAPABILITY_DISABLED'],
                    'decision' => [TransactionEntryScore::ADMIN_CAPABILITY_DENY],
                ]
            );
        }

        $this->audit->record('admin_transaction_capability_used', [
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
                'score' => TransactionEntryScore::ADMIN_CAPABILITY_ALLOW,
            ],
            'Admin diizinkan input transaksi.'
        );
    }
}
