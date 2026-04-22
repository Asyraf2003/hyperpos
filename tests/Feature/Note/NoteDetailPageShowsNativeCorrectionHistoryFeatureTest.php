<?php

declare(strict_types=1);

namespace Tests\Feature\Note;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

final class NoteDetailPageShowsNativeCorrectionHistoryFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_cashier_can_see_native_correction_history_on_note_detail_page(): void
    {
        $this->loginAsKasir();

        $user = User::query()->create([
            'name' => 'Kasir History',
            'email' => 'cashier-history@example.test',
            'password' => 'password',
        ]);

        DB::table('actor_accesses')->insert([
            'actor_id' => (string) $user->getAuthIdentifier(),
            'role' => 'kasir',
        ]);

        $today = now()->toDateString();
        $eventAt = now()->format('Y-m-d H:i:s');

        DB::table('notes')->insert([
            'id' => 'note-1',
            'customer_name' => 'Budi',
            'transaction_date' => $today,
            'total_rupiah' => 45000,
            'note_state' => 'open',
        ]);

        DB::table('note_mutation_events')->insert([
            'id' => 'evt-1',
            'note_id' => 'note-1',
            'mutation_type' => 'paid_service_only_work_item_corrected',
            'actor_id' => 'actor-1',
            'actor_role' => 'admin',
            'reason' => 'Koreksi nominal',
            'occurred_at' => $eventAt,
            'related_customer_payment_id' => null,
            'related_customer_refund_id' => null,
        ]);

        DB::table('note_mutation_snapshots')->insert([
            [
                'id' => 'snap-1',
                'note_mutation_event_id' => 'evt-1',
                'snapshot_kind' => 'before',
                'payload_json' => '{"note":{"total_rupiah":50000},"meta":{"refund_required_rupiah":5000}}',
                'created_at' => $eventAt,
            ],
            [
                'id' => 'snap-2',
                'note_mutation_event_id' => 'evt-1',
                'snapshot_kind' => 'after',
                'payload_json' => '{"note":{"total_rupiah":45000},"meta":{"refund_required_rupiah":5000}}',
                'created_at' => $eventAt,
            ],
        ]);

        $response = $this->actingAs($user)->get(route('cashier.notes.show', ['noteId' => 'note-1']));

        $response->assertOk();
        $response->assertSee('Riwayat Revisi Nota');
        $response->assertSee('Correction Nominal Service');
        $response->assertSee('Koreksi nominal');
        $response->assertSee('actor-1');
        $response->assertSee('50.000', false);
        $response->assertSee('45.000', false);
        $response->assertSee('5.000', false);
    }
}
