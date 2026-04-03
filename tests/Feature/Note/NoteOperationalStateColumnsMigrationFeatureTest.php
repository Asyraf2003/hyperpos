<?php

declare(strict_types=1);

namespace Tests\Feature\Note;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

final class NoteOperationalStateColumnsMigrationFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_notes_table_has_operational_state_columns_with_open_default(): void
    {
        $this->assertTrue(Schema::hasColumn('notes', 'note_state'));
        $this->assertTrue(Schema::hasColumn('notes', 'closed_at'));
        $this->assertTrue(Schema::hasColumn('notes', 'closed_by_actor_id'));
        $this->assertTrue(Schema::hasColumn('notes', 'reopened_at'));
        $this->assertTrue(Schema::hasColumn('notes', 'reopened_by_actor_id'));

        DB::table('notes')->insert([
            'id' => 'note-1',
            'customer_name' => 'Budi',
            'customer_phone' => null,
            'transaction_date' => '2026-04-03',
            'total_rupiah' => 10000,
        ]);

        $row = DB::table('notes')->where('id', 'note-1')->first();

        $this->assertNotNull($row);
        $this->assertSame('open', (string) $row->note_state);
        $this->assertNull($row->closed_at);
        $this->assertNull($row->closed_by_actor_id);
        $this->assertNull($row->reopened_at);
        $this->assertNull($row->reopened_by_actor_id);
    }
}
