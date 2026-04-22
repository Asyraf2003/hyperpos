<?php

declare(strict_types=1);

namespace Tests\Feature\Note;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

final class NoteCorrectionHistoryPageFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_paid_note_detail_page_shows_correction_history_from_native_mutation_timeline(): void
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
            'total_rupiah' => 40000,
        ]);

        DB::table('note_mutation_events')->insert([
            'id' => 'evt-1',
            'note_id' => 'note-1',
            'mutation_type' => 'paid_service_only_work_item_corrected',
            'actor_id' => 'actor-1',
            'actor_role' => 'admin',
            'reason' => 'Harga salah input',
            'occurred_at' => $now,
            'related_customer_payment_id' => null,
            'related_customer_refund_id' => null,
        ]);

        DB::table('note_mutation_snapshots')->insert([
            [
                'id' => 'snap-1',
                'note_mutation_event_id' => 'evt-1',
                'snapshot_kind' => 'before',
                'payload_json' => '{"note":{"total_rupiah":50000},"meta":{"refund_required_rupiah":10000}}',
                'created_at' => $now,
            ],
            [
                'id' => 'snap-2',
                'note_mutation_event_id' => 'evt-1',
                'snapshot_kind' => 'after',
                'payload_json' => '{"note":{"total_rupiah":40000},"meta":{"refund_required_rupiah":10000}}',
                'created_at' => $now,
            ],
        ]);

        $response = $this->actingAs($user)->get('/cashier/notes/note-1');

        $response->assertOk();
        $response->assertSee('Riwayat Revisi Nota');
        $response->assertSee('Correction Nominal Service');
        $response->assertSee('Harga salah input');
        $response->assertSee('actor-1');
        $response->assertSee('50.000', false);
        $response->assertSee('40.000', false);
        $response->assertSee('10.000', false);
    }
}
