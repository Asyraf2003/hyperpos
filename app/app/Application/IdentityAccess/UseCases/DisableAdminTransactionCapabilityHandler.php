<?php

declare(strict_types=1);

namespace App\Application\IdentityAccess\UseCases;

use App\Application\Shared\DTO\Result;
use App\Ports\Out\AuditLogPort;
use App\Ports\Out\IdentityAccess\ActorAccessReaderPort;
use App\Ports\Out\IdentityAccess\AdminTransactionCapabilityStatePort;

final class DisableAdminTransactionCapabilityHandler
{
    public function __construct(
        private readonly ActorAccessReaderPort $actors,
        private readonly AdminTransactionCapabilityStatePort $capabilities,
        private readonly AuditLogPort $audit,
    ) {
    }

    public function handle(string $targetActorId, string $performedByActorId): Result
    {
        $target = $this->actors->findByActorId($targetActorId);

        if ($target === null) {
            return Result::failure(
                'Target admin tidak ditemukan.',
                ['actor' => ['ACTOR_NOT_FOUND']]
            );
        }

        if ($target->isAdmin() === false) {
            return Result::failure(
                'Capability ini hanya berlaku untuk role admin.',
                ['role' => ['ADMIN_ONLY_CAPABILITY']]
            );
        }

        $this->capabilities->deactivate($targetActorId);

        $this->audit->record('admin_transaction_capability_disabled', [
            'target_actor_id' => $targetActorId,
            'performed_by_actor_id' => $performedByActorId,
            'capability' => 'admin_transaction_entry',
        ]);

        return Result::success(
            [
                'actor_id' => $targetActorId,
                'capability' => 'admin_transaction_entry',
                'status' => 'inactive',
            ],
            'Capability input transaksi admin dinonaktifkan.'
        );
    }
}
