<?php

declare(strict_types=1);

namespace Tests\Feature\Note;

use App\Application\Note\UseCases\ReopenClosedNoteHandler;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

final class ReopenClosedNoteFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_reopens_closed_note_and_writes_note_reopened_mutation(): void
    {
        DB::table('notes')->insert([
            'id' => 'note-1',
            'customer_name' => 'Budi',
            'transaction_date' => '2026-04-03',
            'note_state' => 'closed',
            'closed_at' => '2026-04-03 10:00:00',
            'closed_by_actor_id' => 'system',
            'total_rupiah' => 10000,
        ]);

        $result = app(ReopenClosedNoteHandler::class)->handle(
            'note-1',
            'Perlu koreksi setelah review admin',
            'admin-1',
        );

        $this->assertTrue($result->isSuccess());

        $this->assertDatabaseHas('notes', [
            'id' => 'note-1',
            'note_state' => 'open',
            'reopened_by_actor_id' => 'admin-1',
        ]);

        $this->assertDatabaseHas('note_mutation_events', [
            'note_id' => 'note-1',
            'mutation_type' => 'note_reopened',
            'actor_id' => 'admin-1',
            'actor_role' => 'admin',
            'reason' => 'Perlu koreksi setelah review admin',
        ]);

        $eventId = (string) DB::table('note_mutation_events')
            ->where('note_id', 'note-1')
            ->where('mutation_type', 'note_reopened')
            ->value('id');

        $this->assertDatabaseHas('note_mutation_snapshots', [
            'note_mutation_event_id' => $eventId,
            'snapshot_kind' => 'before',
        ]);

        $this->assertDatabaseHas('note_mutation_snapshots', [
            'note_mutation_event_id' => $eventId,
            'snapshot_kind' => 'after',
        ]);
    }
}
