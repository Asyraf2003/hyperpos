<?php

declare(strict_types=1);

namespace App\Adapters\Out\IdentityAccess;

use App\Core\IdentityAccess\Capability\AdminCashierAreaAccessState;
use App\Ports\Out\IdentityAccess\AdminCashierAreaAccessStatePort;
use Illuminate\Support\Facades\DB;

final class DatabaseAdminCashierAreaAccessStateAdapter implements AdminCashierAreaAccessStatePort
{
    public function getByActorId(string $actorId): AdminCashierAreaAccessState
    {
        $row = DB::table('admin_cashier_area_access_states')
            ->select(['actor_id', 'active'])
            ->where('actor_id', $actorId)
            ->first();

        if ($row === null) {
            return AdminCashierAreaAccessState::inactive($actorId);
        }

        return (bool) $row->active
            ? AdminCashierAreaAccessState::active((string) $row->actor_id)
            : AdminCashierAreaAccessState::inactive((string) $row->actor_id);
    }

    public function activate(string $actorId): void
    {
        DB::table('admin_cashier_area_access_states')->updateOrInsert(
            ['actor_id' => $actorId],
            ['active' => true],
        );
    }

    public function deactivate(string $actorId): void
    {
        DB::table('admin_cashier_area_access_states')->updateOrInsert(
            ['actor_id' => $actorId],
            ['active' => false],
        );
    }
}