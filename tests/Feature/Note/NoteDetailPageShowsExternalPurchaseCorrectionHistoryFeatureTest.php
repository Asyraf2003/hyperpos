<?php

declare(strict_types=1);

namespace Tests\Feature\Note;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

final class NoteDetailPageShowsExternalPurchaseCorrectionHistoryFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_note_detail_uses_compact_versioning_instead_of_large_correction_block(): void
    {
        $this->loginAsKasir();

        $user = User::query()->create([
            'name' => 'Kasir',
            'email' => 'cashier@example.test',
            'password' => 'password',
        ]);

        DB::table('actor_accesses')->insert([
            'actor_id' => (string) $user->getAuthIdentifier(),
            'role' => 'kasir',
        ]);

        $today = date('Y-m-d');
        $now = $today . ' 10:00:00';

        DB::table('notes')->insert([
            'id' => 'note-1',
            'customer_name' => 'Budi',
            'transaction_date' => $today,
            'note_state' => 'open',
            'total_rupiah' => 6000,
        ]);

        DB::table('note_mutation_events')->insert([
            'id' => 'evt-1',
            'note_id' => 'note-1',
            'mutation_type' => 'paid_external_purchase_work_item_corrected',
            'actor_id' => 'actor-1',
            'actor_role' => 'admin',
            'reason' => 'Koreksi fee jasa external',
            'occurred_at' => $now,
            'related_customer_payment_id' => null,
            'related_customer_refund_id' => null,
        ]);

        DB::table('note_mutation_snapshots')->insert([
            [
                'id' => 'snap-1',
                'note_mutation_event_id' => 'evt-1',
                'snapshot_kind' => 'before',
                'payload_json' => '{"note":{"total_rupiah":7000}}',
                'created_at' => $now,
            ],
            [
                'id' => 'snap-2',
                'note_mutation_event_id' => 'evt-1',
                'snapshot_kind' => 'after',
                'payload_json' => '{"note":{"total_rupiah":6000}}',
                'created_at' => $now,
            ],
        ]);

        $response = $this->actingAs($user)->get('/cashier/notes/note-1');

        $response->assertOk();
        $response->assertSee('Versioning Nota');
        $response->assertSee('Current Revision');
        $response->assertDontSee('Correction Fee Service + Part External');
    }
}
