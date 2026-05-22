<?php

declare(strict_types=1);

namespace Tests\Feature\AuditLog;

use App\Adapters\Out\Audit\DatabaseAuditOutboxWriterAdapter;
use App\Adapters\Out\Expense\DatabaseExpenseCategoryReaderAdapter;
use App\Adapters\Out\Expense\DatabaseExpenseCategoryWriterAdapter;
use App\Application\Expense\UseCases\ActivateExpenseCategoryHandler;
use App\Application\Expense\UseCases\DeactivateExpenseCategoryHandler;
use App\Application\Expense\UseCases\UpdateExpenseCategoryHandler;
use App\Ports\Out\AuditEventWriterPort;
use App\Ports\Out\ClockPort;
use App\Ports\Out\UuidPort;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

final class AuditOutboxExpenseCategoryRuntimeRegressionTest extends TestCase
{
    use RefreshDatabase;

    public function test_expense_category_canonical_flows_can_stage_and_materialize_with_outbox_binding(): void
    {
        $this->app->bind(AuditEventWriterPort::class, DatabaseAuditOutboxWriterAdapter::class);
        $this->seedCategory('cat-update', 'EXP-ELEC', true);
        $this->seedCategory('cat-activate', 'EXP-ACTV', false);
        $this->seedCategory('cat-deactivate', 'EXP-DEAC', true);

        $reader = new DatabaseExpenseCategoryReaderAdapter();
        $writer = new DatabaseExpenseCategoryWriterAdapter();
        $audit = app(AuditEventWriterPort::class);
        $clock = app(ClockPort::class);
        $uuid = app(UuidPort::class);

        (new UpdateExpenseCategoryHandler($reader, $writer, $audit, $clock, $uuid))
            ->handle('cat-update', 'EXP-UTIL', 'Utilitas', 'Baru', 'admin-1');

        (new ActivateExpenseCategoryHandler($reader, $writer, $audit, $clock, $uuid))
            ->handle('cat-activate', 'admin-1');

        (new DeactivateExpenseCategoryHandler($reader, $writer, $audit, $clock, $uuid))
            ->handle('cat-deactivate', 'admin-1');

        $this->assertDatabaseCount('audit_outbox', 3);
        $this->assertDatabaseCount('audit_events', 0);
        $this->assertDatabaseHas('audit_outbox', ['event_name' => 'expense_category_updated']);
        $this->assertDatabaseHas('audit_outbox', ['event_name' => 'expense_category_activated']);
        $this->assertDatabaseHas('audit_outbox', ['event_name' => 'expense_category_deactivated']);

        $this->artisan('audit:outbox:process', ['--limit' => 10])->assertExitCode(0);

        $this->assertDatabaseCount('audit_events', 3);
        $this->assertDatabaseCount('audit_event_snapshots', 6);
        $this->assertSame(3, DB::table('audit_outbox')->where('status', 'processed')->count());
        $this->assertDatabaseHas('expense_categories', ['id' => 'cat-update', 'code' => 'EXP-UTIL']);
        $this->assertDatabaseHas('expense_categories', ['id' => 'cat-activate', 'is_active' => 1]);
        $this->assertDatabaseHas('expense_categories', ['id' => 'cat-deactivate', 'is_active' => 0]);
    }

    private function seedCategory(string $id, string $code, bool $isActive): void
    {
        DB::table('expense_categories')->insert([
            'id' => $id,
            'code' => $code,
            'name' => 'Kategori',
            'description' => null,
            'is_active' => $isActive,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
