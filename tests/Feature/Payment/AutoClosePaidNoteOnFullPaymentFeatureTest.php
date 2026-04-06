<?php

declare(strict_types=1);

namespace Tests\Feature\Payment;

use App\Application\Payment\UseCases\RecordAndAllocateNotePaymentHandler;
use App\Core\Note\WorkItem\WorkItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Support\SeedsMinimalNotePaymentFixture;
use Tests\TestCase;

final class AutoClosePaidNoteOnFullPaymentFeatureTest extends TestCase
{
    use RefreshDatabase;
    use SeedsMinimalNotePaymentFixture;

    public function test_it_auto_closes_note_and_writes_note_closed_mutation_when_full_payment_is_reached(): void
    {
        $this->seedNotePaymentProduct('product-1', 'KB-001', 'Ban Luar', 'Federal', 100, 10000);
        $this->seedNoteBase('note-1', 'Budi', '2026-04-03', 10000, 'open');
        $this->seedWorkItemBase('wi-1', 'note-1', 1, WorkItem::TYPE_STORE_STOCK_SALE_ONLY, WorkItem::STATUS_OPEN, 10000);
        $this->seedStoreStockLineBase('sto-1', 'wi-1', 'product-1', 1, 10000);

        $result = app(RecordAndAllocateNotePaymentHandler::class)->handle('note-1', 10000, '2026-04-03');

        $this->assertTrue($result->isSuccess());

        $paymentId = (string) DB::table('customer_payments')->value('id');

        $this->assertDatabaseHas('notes', [
            'id' => 'note-1',
            'note_state' => 'closed',
            'closed_by_actor_id' => 'system',
        ]);

        $this->assertDatabaseHas('note_mutation_events', [
            'note_id' => 'note-1',
            'mutation_type' => 'note_closed',
            'actor_id' => 'system',
            'actor_role' => 'system',
            'reason' => 'AUTO_CLOSE_ON_FULL_PAYMENT',
            'related_customer_payment_id' => $paymentId,
        ]);

        $eventId = (string) DB::table('note_mutation_events')
            ->where('note_id', 'note-1')
            ->where('mutation_type', 'note_closed')
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
