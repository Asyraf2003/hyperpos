<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Core\IdentityAccess\Role\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

final class UserSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::query()->updateOrCreate(
            ['email' => 'admin@gmail.com'],
            [
                'name' => 'pak bos',
                'password' => Hash::make('t37rt762gr67324rtgf4g74gyf'),
            ],
        );

        $kasir = User::query()->updateOrCreate(
            ['email' => 'kasir@gmail.com'],
            [
                'name' => 'Liya',
                'password' => Hash::make('12345678'),
            ],
        );

        DB::table('actor_accesses')->upsert([
            [
                'actor_id' => (string) $admin->id,
                'role' => Role::ADMIN,
            ],
            [
                'actor_id' => (string) $kasir->id,
                'role' => Role::KASIR,
            ],
        ], ['actor_id'], ['role']);

        DB::table('admin_cashier_area_access_states')->upsert([
            [
                'actor_id' => (string) $admin->id,
                'active' => true,
            ],
        ], ['actor_id'], ['active']);
    }
}