<?php

declare(strict_types=1);

namespace Tests\Feature\Note;

use App\Application\Note\Services\NoteCorrectionHistoryBuilder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

final class NoteCorrectionHistoryBuilderFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_reads_correction_history_from_native_mutation_timeline(): void
    {
        DB::table('note_mutation_events')->insert([
            'id' => 'evt-1',
            'note_id' => 'note-1',
            'mutation_type' => 'paid_service_only_work_item_corrected',
            'actor_id' => 'actor-1',
            'actor_role' => 'admin',
            'reason' => 'Koreksi nominal',
            'occurred_at' => '2026-04-02 10:00:00',
            'related_customer_payment_id' => null,
            'related_customer_refund_id' => null,
        ]);

        DB::table('note_mutation_snapshots')->insert([
            [
                'id' => 'snap-1',
                'note_mutation_event_id' => 'evt-1',
                'snapshot_kind' => 'before',
                'payload_json' => '{"note":{"total_rupiah":50000},"meta":{"refund_required_rupiah":5000}}',
                'created_at' => '2026-04-02 10:00:00',
            ],
            [
                'id' => 'snap-2',
                'note_mutation_event_id' => 'evt-1',
                'snapshot_kind' => 'after',
                'payload_json' => '{"note":{"total_rupiah":45000},"meta":{"refund_required_rupiah":5000}}',
                'created_at' => '2026-04-02 10:00:00',
            ],
        ]);

        $history = app(NoteCorrectionHistoryBuilder::class)->build('note-1');

        $this->assertCount(1, $history);
        $this->assertSame('Correction Nominal Service', $history[0]['event_label']);
        $this->assertSame('2026-04-02 10:00:00', $history[0]['created_at']);
        $this->assertSame('Koreksi nominal', $history[0]['reason']);
        $this->assertSame('actor-1', $history[0]['performed_by_actor_id']);
        $this->assertSame(50000, $history[0]['before_total_rupiah']);
        $this->assertSame(45000, $history[0]['after_total_rupiah']);
        $this->assertSame(5000, $history[0]['refund_required_rupiah']);
    }
}
