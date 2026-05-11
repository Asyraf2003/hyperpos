<?php

declare(strict_types=1);

namespace Tests\Feature\Seeder;

use App\Models\User;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use RuntimeException;
use Tests\TestCase;

final class UserSeederCredentialBoundaryFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_local_environment_allows_predictable_local_seeded_users(): void
    {
        $this->forceApplicationEnvironment('local');

        $this->seed(UserSeeder::class);

        $admin = User::query()->where('email', 'admin@gmail.com')->firstOrFail();
        $kasir = User::query()->where('email', 'kasir@gmail.com')->firstOrFail();

        self::assertTrue(Hash::check('12345678', (string) $admin->password));
        self::assertTrue(Hash::check('12345678', (string) $kasir->password));
    }

    public function test_testing_environment_allows_predictable_local_seeded_users(): void
    {
        $this->forceApplicationEnvironment('testing');

        $this->seed(UserSeeder::class);

        $admin = User::query()->where('email', 'admin@gmail.com')->firstOrFail();
        $kasir = User::query()->where('email', 'kasir@gmail.com')->firstOrFail();

        self::assertTrue(Hash::check('12345678', (string) $admin->password));
        self::assertTrue(Hash::check('12345678', (string) $kasir->password));
    }

    public function test_staging_environment_blocks_predictable_seeded_users(): void
    {
        $this->forceApplicationEnvironment('staging');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Predictable seeded users are only allowed in local/testing environments.');

        try {
            $this->seed(UserSeeder::class);
        } finally {
            self::assertFalse(User::query()->where('email', 'admin@gmail.com')->exists());
            self::assertFalse(User::query()->where('email', 'kasir@gmail.com')->exists());
        }
    }

    public function test_unknown_environment_blocks_predictable_seeded_users(): void
    {
        $this->forceApplicationEnvironment('owner-visible-qa');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Predictable seeded users are only allowed in local/testing environments.');

        try {
            $this->seed(UserSeeder::class);
        } finally {
            self::assertFalse(User::query()->where('email', 'admin@gmail.com')->exists());
            self::assertFalse(User::query()->where('email', 'kasir@gmail.com')->exists());
        }
    }

    private function forceApplicationEnvironment(string $environment): void
    {
        $this->app->detectEnvironment(static fn (): string => $environment);
    }
}
