<?php

declare(strict_types=1);

namespace Tests\Feature\Note;

use App\Application\Note\Services\NoteDetailPageDataBuilder;
use App\Core\Note\WorkItem\ServiceDetail;
use App\Core\Note\WorkItem\WorkItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Support\SeedsMinimalNotePaymentFixture;
use Tests\TestCase;

final class NoteDetailSurplusDispositionPayloadFeatureTest extends TestCase
{
    use RefreshDatabase;
    use SeedsMinimalNotePaymentFixture;

    public function test_detail_payload_exposes_pending_surplus_refund_due_action(): void
    {
        $this->seedClosedRevisedNoteWithSettlement(
            noteId: 'note-surplus-payload-001',
            workItemId: 'wi-surplus-payload-001',
            revisionId: 'revision-surplus-payload-001',
            settlementId: 'settlement-surplus-payload-001',
            surplusRupiah: 122000,
            status: 'overpaid_pending',
        );

        $payload = $this->app->make(NoteDetailPageDataBuilder::class)
            ->build('note-surplus-payload-001');

        self::assertNotNull($payload);

        $surplus = $payload['note']['surplus_disposition'] ?? null;

        self::assertIsArray($surplus);
        self::assertTrue($surplus['has_pending_refund_due_action']);
        self::assertCount(1, $surplus['pending_items']);

        $item = $surplus['pending_items'][0];

        self::assertSame('settlement-surplus-payload-001', $item['note_revision_settlement_id']);
        self::assertSame('revision-surplus-payload-001', $item['note_revision_id']);
        self::assertSame('note-surplus-payload-001', $item['note_root_id']);
        self::assertSame(122000, $item['surplus_rupiah']);
        self::assertSame(0, $item['active_disposition_rupiah']);
        self::assertSame(122000, $item['unresolved_pending_rupiah']);
        self::assertSame('refund_due', $item['disposition_type']);
        self::assertSame(122000, $item['amount_default_rupiah']);
        self::assertTrue($item['reason_required']);
        self::assertArrayNotHasKey('action_url', $item);
        self::assertFalse($surplus['has_pending_refund_paid_action']);
        self::assertSame([], $surplus['refund_paid_items']);
    }

    public function test_detail_payload_has_empty_surplus_action_when_no_pending_surplus_exists(): void
    {
        $this->seedClosedRevisedNoteWithoutSettlement(
            noteId: 'note-surplus-payload-002',
            workItemId: 'wi-surplus-payload-002',
            revisionId: 'revision-surplus-payload-002',
        );

        $payload = $this->app->make(NoteDetailPageDataBuilder::class)
            ->build('note-surplus-payload-002');

        self::assertNotNull($payload);

        $surplus = $payload['note']['surplus_disposition'] ?? null;

        self::assertSame([
            'has_pending_refund_due_action' => false,
            'pending_items' => [],
            'has_pending_refund_paid_action' => false,
            'refund_paid_items' => [],
        ], $surplus);
    }

    private function seedClosedRevisedNoteWithSettlement(
        string $noteId,
        string $workItemId,
        string $revisionId,
        string $settlementId,
        int $surplusRupiah,
        string $status,
    ): void {
        $this->seedClosedRevisedNoteWithoutSettlement($noteId, $workItemId, $revisionId);

        DB::table('note_revision_settlements')->insert([
            'id' => $settlementId,
            'note_revision_id' => $revisionId,
            'note_root_id' => $noteId,
            'gross_total_rupiah' => 143000,
            'carry_forward_paid_rupiah' => 265000,
            'carry_forward_refunded_rupiah' => 0,
            'net_paid_rupiah' => 265000,
            'outstanding_rupiah' => 0,
            'surplus_rupiah' => $surplusRupiah,
            'settlement_status' => $status,
            'created_at' => '2026-05-13 09:30:00',
            'updated_at' => null,
        ]);
    }

    private function seedClosedRevisedNoteWithoutSettlement(
        string $noteId,
        string $workItemId,
        string $revisionId,
    ): void {
        $transactionDate = '2026-05-13';

        $this->seedNoteBase($noteId, 'Customer Surplus Payload', $transactionDate, 143000, 'closed');
        $this->seedWorkItemBase(
            $workItemId,
            $noteId,
            1,
            WorkItem::TYPE_SERVICE_ONLY,
            WorkItem::STATUS_OPEN,
            143000,
        );
        $this->seedServiceDetailBase(
            $workItemId,
            'Servis Surplus Payload',
            143000,
            ServiceDetail::PART_SOURCE_NONE,
        );
        $this->seedServiceOnlyCurrentRevision(
            $noteId,
            $revisionId,
            $workItemId,
            'Customer Surplus Payload',
            $transactionDate,
            143000,
            'Servis Surplus Payload',
            143000,
        );
    }
}
