<?php

declare(strict_types=1);

namespace Tests\Feature\Note;

use App\Core\Note\WorkItem\ServiceDetail;
use App\Core\Note\WorkItem\WorkItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

final class CashierNoteMutationHistoryViewFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_note_detail_shows_mutation_history_wording(): void
    {
        $user = $this->seedKasir();
        $today = date('Y-m-d');

        DB::table('notes')->insert([
            'id' => 'note-1',
            'customer_name' => 'Budi',
            'customer_phone' => null,
            'transaction_date' => $today,
            'note_state' => 'open',
            'closed_at' => null,
            'closed_by_actor_id' => null,
            'reopened_at' => null,
            'reopened_by_actor_id' => null,
            'total_rupiah' => 45000,
        ]);

        DB::table('work_items')->insert([
            'id' => 'wi-1',
            'note_id' => 'note-1',
            'line_no' => 1,
            'transaction_type' => WorkItem::TYPE_SERVICE_ONLY,
            'status' => WorkItem::STATUS_OPEN,
            'subtotal_rupiah' => 45000,
        ]);

        DB::table('work_item_service_details')->insert([
            'work_item_id' => 'wi-1',
            'service_name' => 'Servis A',
            'service_price_rupiah' => 45000,
            'part_source' => ServiceDetail::PART_SOURCE_NONE,
        ]);

        DB::table('note_mutation_events')->insert([
            'id' => 'evt-1',
            'note_id' => 'note-1',
            'mutation_type' => 'paid_service_only_work_item_corrected',
            'actor_id' => 'actor-1',
            'actor_role' => 'admin',
            'reason' => 'Koreksi nominal',
            'occurred_at' => $today . ' 10:00:00',
            'related_customer_payment_id' => null,
            'related_customer_refund_id' => null,
        ]);

        DB::table('note_mutation_snapshots')->insert([
            [
                'id' => 'snap-1',
                'note_mutation_event_id' => 'evt-1',
                'snapshot_kind' => 'before',
                'payload_json' => '{"note":{"total_rupiah":50000},"meta":{"refund_required_rupiah":5000}}',
                'created_at' => $today . ' 10:00:00',
            ],
            [
                'id' => 'snap-2',
                'note_mutation_event_id' => 'evt-1',
                'snapshot_kind' => 'after',
                'payload_json' => '{"note":{"total_rupiah":45000},"meta":{"refund_required_rupiah":5000}}',
                'created_at' => $today . ' 10:00:00',
            ],
        ]);

        $this->actingAs($user)
            ->get(route('cashier.notes.show', ['noteId' => 'note-1']))
            ->assertOk()
            ->assertSee('Riwayat Revisi Nota')
            ->assertSee('Diproses oleh:')
            ->assertDontSee('Riwayat Correction');
    }

    private function seedKasir(): User
    {
        $this->loginAsKasir();

        $user = User::query()->create([
            'name' => 'Kasir Mutation History',
            'email' => 'cashier-mutation-history@example.test',
            'password' => 'password',
        ]);

        DB::table('actor_accesses')->insert([
            'actor_id' => (string) $user->getAuthIdentifier(),
            'role' => 'kasir',
        ]);

        return $user;
    }
}
