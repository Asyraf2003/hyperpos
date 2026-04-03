<?php

declare(strict_types=1);

namespace Tests\Feature\Payment;

use App\Application\Payment\UseCases\RecordAndAllocateNotePaymentHandler;
use App\Core\Note\WorkItem\WorkItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

final class AutoClosePaidNoteOnFullPaymentFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_auto_closes_note_and_writes_note_closed_mutation_when_full_payment_is_reached(): void
    {
        DB::table('notes')->insert([
            'id' => 'note-1',
            'customer_name' => 'Budi',
            'transaction_date' => '2026-04-03',
            'note_state' => 'open',
            'total_rupiah' => 10000,
        ]);

        DB::table('work_items')->insert([
            'id' => 'wi-1',
            'note_id' => 'note-1',
            'line_no' => 1,
            'transaction_type' => WorkItem::TYPE_STORE_STOCK_SALE_ONLY,
            'status' => WorkItem::STATUS_OPEN,
            'subtotal_rupiah' => 10000,
        ]);

        DB::table('work_item_store_stock_lines')->insert([
            'id' => 'sto-1',
            'work_item_id' => 'wi-1',
            'product_id' => 'product-1',
            'qty' => 1,
            'line_total_rupiah' => 10000,
        ]);

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
