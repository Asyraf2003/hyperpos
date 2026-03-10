<?php

declare(strict_types=1);

namespace App\Adapters\Out\IdentityAccess;

use App\Core\IdentityAccess\Capability\AdminTransactionCapabilityState;
use App\Ports\Out\IdentityAccess\AdminTransactionCapabilityStatePort;
use Illuminate\Support\Facades\DB;

final class DatabaseAdminTransactionCapabilityStateAdapter implements AdminTransactionCapabilityStatePort
{
    public function getByActorId(string $actorId): AdminTransactionCapabilityState
    {
        $row = DB::table('admin_transaction_capability_states')
            ->select(['actor_id', 'active'])
            ->where('actor_id', $actorId)
            ->first();

        if ($row === null) {
            return AdminTransactionCapabilityState::inactive($actorId);
        }

        return (bool) $row->active
            ? AdminTransactionCapabilityState::active((string) $row->actor_id)
            : AdminTransactionCapabilityState::inactive((string) $row->actor_id);
    }

    public function activate(string $actorId): void
    {
        DB::table('admin_transaction_capability_states')->updateOrInsert(
            ['actor_id' => $actorId],
            ['active' => true],
        );
    }

    public function deactivate(string $actorId): void
    {
        DB::table('admin_transaction_capability_states')->updateOrInsert(
            ['actor_id' => $actorId],
            ['active' => false],
        );
    }
}
