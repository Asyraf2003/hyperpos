<?php
declare(strict_types=1);

namespace Tests\Feature\Expense;

use App\Adapters\Out\Expense\DatabaseExpenseCategoryReaderAdapter;
use App\Adapters\Out\Expense\DatabaseExpenseCategoryWriterAdapter;
use App\Application\Expense\UseCases\UpdateExpenseCategoryHandler;
use App\Ports\Out\AuditEventWriterPort;
use App\Ports\Out\ClockPort;
use App\Ports\Out\UuidPort;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

final class UpdateExpenseCategoryFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_update_expense_category_updates_row_and_records_canonical_audit(): void
    {
        $this->seedCategory('cat-1', 'EXP-ELEC', 'Listrik', true, 'Lama');
        $result = $this->handler()->handle('cat-1', 'EXP-UTIL', 'Utilitas', 'Baru', 'admin-1');

        $this->assertTrue($result->isSuccess());
        $this->assertDatabaseHas('expense_categories', [
            'id' => 'cat-1',
            'code' => 'EXP-UTIL',
            'name' => 'Utilitas',
            'description' => 'Baru',
            'is_active' => 1,
        ]);
        $outbox = DB::table('audit_outbox')->where('event_name', 'expense_category_updated')->first();
        self::assertNotNull($outbox);
        self::assertSame('pending', $outbox->status);
        self::assertSame('cat-1', $outbox->aggregate_id);
        self::assertSame('admin-1', $outbox->actor_id);
        $this->assertDatabaseCount('audit_events', 0);
        $this->assertDatabaseCount('audit_event_snapshots', 0);

        $this->artisan('audit:outbox:process', ['--limit' => 10])->assertExitCode(0);

        $this->assertDatabaseHas('audit_events', [
            'id' => $outbox->audit_event_id,
            'event_name' => 'expense_category_updated',
            'actor_id' => 'admin-1',
        ]);
        foreach (['before', 'after'] as $kind) {
            $this->assertDatabaseHas('audit_event_snapshots', [
                'audit_event_id' => $outbox->audit_event_id,
                'snapshot_kind' => $kind,
            ]);
        }
        $this->assertDatabaseHas('audit_outbox', [
            'audit_event_id' => $outbox->audit_event_id,
            'status' => 'processed',
        ]);
    }

    public function test_update_expense_category_rejects_duplicate_code(): void
    {
        $this->seedCategory('cat-1', 'EXP-ELEC', 'Listrik', true, null);
        $this->seedCategory('cat-2', 'EXP-WIFI', 'Wifi', true, null);
        $result = $this->handler()->handle('cat-1', 'EXP-WIFI', 'Utilitas', null, 'admin-1');

        $this->assertTrue($result->isFailure());
        $this->assertSame(['expense_category' => ['EXPENSE_CATEGORY_CODE_ALREADY_EXISTS']], $result->errors());
        $this->assertDatabaseCount('audit_outbox', 0);
        $this->assertDatabaseCount('audit_events', 0);
        $this->assertDatabaseCount('audit_event_snapshots', 0);
    }

    private function handler(): UpdateExpenseCategoryHandler
    {
        return new UpdateExpenseCategoryHandler(
            new DatabaseExpenseCategoryReaderAdapter(),
            new DatabaseExpenseCategoryWriterAdapter(),
            app(AuditEventWriterPort::class),
            app(ClockPort::class),
            app(UuidPort::class),
        );
    }

    private function seedCategory(string $id, string $code, string $name, bool $isActive, ?string $description): void
    {
        DB::table('expense_categories')->insert([
            'id' => $id,
            'code' => $code,
            'name' => $name,
            'description' => $description,
            'is_active' => $isActive,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
