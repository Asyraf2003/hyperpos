<?php

declare(strict_types=1);

namespace Tests\Feature\AuditLog;

use App\Adapters\Out\Audit\DatabaseAuditOutboxWriterAdapter;
use App\Ports\Out\AuditEventWriterPort;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

final class AuditOutboxRuntimeBindingTest extends TestCase
{
    use RefreshDatabase;

    public function test_audit_event_writer_port_resolves_to_outbox_writer(): void
    {
        $writer = app(AuditEventWriterPort::class);

        $this->assertInstanceOf(DatabaseAuditOutboxWriterAdapter::class, $writer);
    }

    public function test_expense_category_http_update_stages_audit_then_processor_materializes(): void
    {
        $this->seedCategory('cat-1');

        $response = $this->actingAs($this->user('admin'))
            ->put(route('admin.expenses.categories.update', ['categoryId' => 'cat-1']), [
                'code' => 'EXP-UTIL',
                'name' => 'Utilitas',
                'description' => 'Baru',
            ]);

        $response->assertRedirect(route('admin.expenses.categories.index'));
        $this->assertDatabaseHas('expense_categories', [
            'id' => 'cat-1',
            'code' => 'EXP-UTIL',
            'name' => 'Utilitas',
        ]);
        $this->assertDatabaseHas('audit_outbox', [
            'event_name' => 'expense_category_updated',
            'aggregate_id' => 'cat-1',
            'status' => 'pending',
        ]);
        $this->assertDatabaseCount('audit_events', 0);

        $this->artisan('audit:outbox:process', ['--limit' => 10])->assertExitCode(0);

        $this->assertDatabaseHas('audit_events', [
            'event_name' => 'expense_category_updated',
            'aggregate_id' => 'cat-1',
        ]);
        $this->assertSame(1, DB::table('audit_outbox')->where('status', 'processed')->count());
    }

    private function user(string $role): object
    {
        $userClass = 'App\\Adapters\\Out\\Persistence\\Eloquent\\IdentityAccess\\EloquentUser';
        $user = $userClass::query()->create([
            'name' => 'Test User',
            'email' => $role . '-audit-outbox-runtime-binding@example.test',
            'password' => 'password123',
        ]);

        DB::table('actor_accesses')->insert([
            'actor_id' => (string) $user->getAuthIdentifier(),
            'role' => $role,
        ]);

        return $user;
    }

    private function seedCategory(string $id): void
    {
        DB::table('expense_categories')->insert([
            'id' => $id,
            'code' => 'EXP-ELEC',
            'name' => 'Listrik',
            'description' => 'Lama',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
