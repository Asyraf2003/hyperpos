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
        // 1. Buat User Admin (Owner)
        $admin = User::create([
            'name' => 'Asyraf Mubarak',
            'email' => 'asyraf@bengkel.id',
            'password' => Hash::make('password_admin'),
        ]);

        // 2. Buat User Kasir
        $kasir = User::create([
            'name' => 'Liya',
            'email' => 'liya@bengkel.id',
            'password' => Hash::make('password_kasir'),
        ]);

        // 3. Petakan ke Actor Access (Domain Otorisasi)
        // actor_id di migrasi Anda adalah string, jadi kita cast ID-nya.
        DB::table('actor_accesses')->insert([
            [
                'actor_id' => (string) $admin->id,
                'role' => Role::ADMIN,
            ],
            [
                'actor_id' => (string) $kasir->id,
                'role' => Role::KASIR,
            ],
        ]);
    }
}
