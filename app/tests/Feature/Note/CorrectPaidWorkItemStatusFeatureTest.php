<?php

declare(strict_types=1);

namespace Tests\Feature\Note;

use App\Application\Note\UseCases\CorrectPaidWorkItemStatusHandler;
use App\Application\Shared\DTO\Result;
use App\Core\Note\WorkItem\ServiceDetail;
use App\Core\Note\WorkItem\WorkItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

final class CorrectPaidWorkItemStatusFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_correct_paid_work_item_status_handler_updates_status_with_reason_and_audit(): void
    {
        $this->loginAsKasir();
        $this->seedPaidNoteWithOpenWorkItem();

        $handler = app(CorrectPaidWorkItemStatusHandler::class);

        $result = $handler->handle(
            'note-1',
            1,
            WorkItem::STATUS_DONE,
            'Salah input status, seharusnya sudah selesai.',
            'owner-1',
        );

        $this->assertInstanceOf(Result::class, $result);
        $this->assertTrue($result->isSuccess());

        $this->assertDatabaseHas('work_items', [
            'id' => 'work-item-1',
            'note_id' => 'note-1',
            'line_no' => 1,
            'status' => WorkItem::STATUS_DONE,
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'event' => 'paid_work_item_status_corrected',
        ]);

        $audit = DB::table('audit_logs')
            ->where('event', 'paid_work_item_status_corrected')
            ->latest('id')
            ->first();

        $this->assertNotNull($audit);

        $context = json_decode((string) $audit->context, true, 512, JSON_THROW_ON_ERROR);

        $this->assertSame('owner-1', $context['performed_by_actor_id']);
        $this->assertSame('note-1', $context['note_id']);
        $this->assertSame(1, $context['line_no']);
        $this->assertSame(WorkItem::STATUS_DONE, $context['target_status']);
        $this->assertSame('Salah input status, seharusnya sudah selesai.', $context['reason']);

        $this->assertSame(WorkItem::STATUS_OPEN, $context['before']['work_items'][0]['status']);
        $this->assertSame(WorkItem::STATUS_DONE, $context['after']['work_items'][0]['status']);
    }

    public function test_correct_paid_work_item_status_handler_rejects_blank_reason(): void
    {
        $this->loginAsKasir();
        $this->seedPaidNoteWithOpenWorkItem();

        $handler = app(CorrectPaidWorkItemStatusHandler::class);

        $result = $handler->handle(
            'note-1',
            1,
            WorkItem::STATUS_DONE,
            '   ',
            'owner-1',
        );

        $this->assertInstanceOf(Result::class, $result);
        $this->assertTrue($result->isFailure());
        $this->assertSame(
            ['correction' => ['AUDIT_REASON_REQUIRED']],
            $result->errors(),
        );

        $this->assertDatabaseHas('work_items', [
            'id' => 'work-item-1',
            'status' => WorkItem::STATUS_OPEN,
        ]);

        $this->assertDatabaseMissing('audit_logs', [
            'event' => 'paid_work_item_status_corrected',
        ]);
    }

    public function test_correct_paid_work_item_status_handler_rejects_unpaid_note(): void
    {
        $this->loginAsKasir();
        $this->seedUnpaidNoteWithOpenWorkItem();

        $handler = app(CorrectPaidWorkItemStatusHandler::class);

        $result = $handler->handle(
            'note-1',
            1,
            WorkItem::STATUS_DONE,
            'Salah input status.',
            'owner-1',
        );

        $this->assertInstanceOf(Result::class, $result);
        $this->assertTrue($result->isFailure());
        $this->assertSame(
            ['work_item' => ['INVALID_WORK_ITEM_STATE']],
            $result->errors(),
        );

        $this->assertDatabaseHas('work_items', [
            'id' => 'work-item-1',
            'status' => WorkItem::STATUS_OPEN,
        ]);

        $this->assertDatabaseMissing('audit_logs', [
            'event' => 'paid_work_item_status_corrected',
        ]);
    }

    private function seedPaidNoteWithOpenWorkItem(): void
    {
        $this->seedNote('note-1', 'Budi Santoso', '2026-03-14', 30000);
        $this->seedServiceOnlyWorkItem('work-item-1', 'note-1', 1, 30000, WorkItem::STATUS_OPEN);
        $this->seedCustomerPayment('payment-1', 30000, '2026-03-15');
        $this->seedPaymentAllocation('allocation-1', 'payment-1', 'note-1', 30000);
    }

    private function seedUnpaidNoteWithOpenWorkItem(): void
    {
        $this->seedNote('note-1', 'Budi Santoso', '2026-03-14', 30000);
        $this->seedServiceOnlyWorkItem('work-item-1', 'note-1', 1, 30000, WorkItem::STATUS_OPEN);
    }

    private function seedNote(
        string $id,
        string $customerName,
        string $transactionDate,
        int $totalRupiah,
    ): void {
        DB::table('notes')->insert([
            'id' => $id,
            'customer_name' => $customerName,
            'transaction_date' => $transactionDate,
            'total_rupiah' => $totalRupiah,
        ]);
    }

    private function seedServiceOnlyWorkItem(
        string $id,
        string $noteId,
        int $lineNo,
        int $subtotalRupiah,
        string $status,
    ): void {
        DB::table('work_items')->insert([
            'id' => $id,
            'note_id' => $noteId,
            'line_no' => $lineNo,
            'transaction_type' => WorkItem::TYPE_SERVICE_ONLY,
            'status' => $status,
            'subtotal_rupiah' => $subtotalRupiah,
        ]);

        DB::table('work_item_service_details')->insert([
            'work_item_id' => $id,
            'service_name' => 'Servis Karburator',
            'service_price_rupiah' => $subtotalRupiah,
            'part_source' => ServiceDetail::PART_SOURCE_NONE,
        ]);
    }

    private function seedCustomerPayment(
        string $id,
        int $amountRupiah,
        string $paidAt,
    ): void {
        DB::table('customer_payments')->insert([
            'id' => $id,
            'amount_rupiah' => $amountRupiah,
            'paid_at' => $paidAt,
        ]);
    }

    private function seedPaymentAllocation(
        string $id,
        string $customerPaymentId,
        string $noteId,
        int $amountRupiah,
    ): void {
        DB::table('payment_allocations')->insert([
            'id' => $id,
            'customer_payment_id' => $customerPaymentId,
            'note_id' => $noteId,
            'amount_rupiah' => $amountRupiah,
        ]);
    }
}
