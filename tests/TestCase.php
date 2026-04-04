<?php

namespace Tests;

use App\Models\User;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\DB;

abstract class TestCase extends BaseTestCase
{
    /**
     * Memenuhi Kontrak Identity & Access (Blueprint 1.3.1)
     * Menciptakan User Laravel + Actor Access Domain dengan Role Kasir.
     */
    protected function loginAsKasir(): User
    {
        $user = User::factory()->create();

        DB::table('actor_accesses')->insert([
            'actor_id' => $user->getAuthIdentifier(),
            'role' => 'kasir',
        ]);

        $this->actingAs($user);

        return $user;
    }

    /**
     * Menciptakan Admin yang memiliki kapabilitas transaksi aktif.
     */
    protected function loginAsAuthorizedAdmin(): User
    {
        $user = User::factory()->create();

        DB::table('actor_accesses')->insert([
            'actor_id' => $user->getAuthIdentifier(),
            'role' => 'admin',
        ]);

        DB::table('admin_transaction_capability_states')->updateOrInsert(
            ['actor_id' => (string) $user->getAuthIdentifier()],
            ['active' => true],
        );

        $this->actingAs($user);

        return $user;
    }
}
