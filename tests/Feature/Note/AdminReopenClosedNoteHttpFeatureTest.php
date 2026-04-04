<?php

declare(strict_types=1);

namespace Tests\Feature\Note;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

final class AdminReopenClosedNoteHttpFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_authorized_admin_can_reopen_closed_note_from_admin_detail_page(): void
    {
        $admin = $this->loginAsAuthorizedAdmin();
        $closedAt = now()->format('Y-m-d H:i:s');

        DB::table('notes')->insert([
            'id' => 'note-1',
            'customer_name' => 'Budi',
            'transaction_date' => now()->toDateString(),
            'total_rupiah' => 50000,
            'note_state' => 'closed',
            'closed_at' => $closedAt,
            'closed_by_actor_id' => 'admin-legacy',
        ]);

        $response = $this->post(route('admin.notes.reopen', ['noteId' => 'note-1']), [
            'reason' => 'Perlu edit lanjutan oleh admin.',
        ]);

        $response->assertRedirect(route('admin.notes.show', ['noteId' => 'note-1']));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('notes', [
            'id' => 'note-1',
            'note_state' => 'open',
            'reopened_by_actor_id' => (string) $admin->getAuthIdentifier(),
        ]);

        $this->assertDatabaseHas('note_mutation_events', [
            'note_id' => 'note-1',
            'mutation_type' => 'note_reopened',
            'actor_id' => (string) $admin->getAuthIdentifier(),
            'actor_role' => 'admin',
            'reason' => 'Perlu edit lanjutan oleh admin.',
        ]);
    }

    public function test_authorized_admin_cannot_reopen_closed_note_without_reason(): void
    {
        $this->loginAsAuthorizedAdmin();
        $closedAt = now()->format('Y-m-d H:i:s');

        DB::table('notes')->insert([
            'id' => 'note-2',
            'customer_name' => 'Andi',
            'transaction_date' => now()->toDateString(),
            'total_rupiah' => 30000,
            'note_state' => 'closed',
            'closed_at' => $closedAt,
            'closed_by_actor_id' => 'admin-legacy',
        ]);

        $response = $this->from(route('admin.notes.show', ['noteId' => 'note-2']))
            ->post(route('admin.notes.reopen', ['noteId' => 'note-2']), [
                'reason' => '',
            ]);

        $response->assertRedirect(route('admin.notes.show', ['noteId' => 'note-2']));
        $response->assertSessionHasErrors(['reason']);

        $this->assertDatabaseHas('notes', [
            'id' => 'note-2',
            'note_state' => 'closed',
        ]);

        $this->assertDatabaseMissing('note_mutation_events', [
            'note_id' => 'note-2',
            'mutation_type' => 'note_reopened',
        ]);
    }
}
