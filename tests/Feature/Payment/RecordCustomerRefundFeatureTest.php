<?php

declare(strict_types=1);

namespace Tests\Feature\Payment;

use App\Adapters\Out\Audit\DatabaseAuditLogAdapter;
use App\Adapters\Out\Note\DatabaseNoteReaderAdapter;
use App\Adapters\Out\Payment\DatabaseCustomerPaymentReaderAdapter;
use App\Adapters\Out\Payment\DatabaseCustomerRefundReaderAdapter;
use App\Adapters\Out\Payment\DatabaseCustomerRefundWriterAdapter;
use App\Adapters\Out\Payment\DatabasePaymentAllocationReaderAdapter;
use App\Application\Payment\UseCases\RecordCustomerRefundHandler;
use App\Application\Shared\DTO\Result;
use App\Ports\Out\TransactionManagerPort;
use App\Ports\Out\UuidPort;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

final class RecordCustomerRefundFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_record_customer_refund_handler_stores_refund_and_writes_audit_log(): void
    {
        $this->seedNote('note-1', 50000);
        $this->seedCustomerPayment('payment-1', 50000, '2026-03-15');
        $this->seedPaymentAllocation('allocation-1', 'payment-1', 'note-1', 50000);

        $result = $this->buildHandler('refund-1')->handle('payment-1', 'note-1', 20000, '2026-03-16', 'Salah input total.', 'owner-1');

        $this->assertInstanceOf(Result::class, $result);
        $this->assertTrue($result->isSuccess());
        $this->assertDatabaseHas('customer_refunds', ['id' => 'refund-1', 'customer_payment_id' => 'payment-1', 'note_id' => 'note-1', 'amount_rupiah' => 20000, 'refunded_at' => '2026-03-16', 'reason' => 'Salah input total.']);
        $this->assertDatabaseHas('audit_logs', ['event' => 'customer_refund_recorded']);

        $audit = DB::table('audit_logs')->where('event', 'customer_refund_recorded')->latest('id')->first();
        $this->assertNotNull($audit);

        $context = json_decode((string) $audit->context, true, 512, JSON_THROW_ON_ERROR);
        $this->assertSame('refund-1', $context['refund_id']);
        $this->assertSame('payment-1', $context['customer_payment_id']);
        $this->assertSame('note-1', $context['note_id']);
        $this->assertSame(20000, $context['amount_rupiah']);
        $this->assertSame('2026-03-16', $context['refunded_at']);
        $this->assertSame('Salah input total.', $context['reason']);
        $this->assertSame('owner-1', $context['performed_by_actor_id']);
    }

    public function test_record_customer_refund_handler_rejects_blank_reason(): void
    {
        $this->seedNote('note-1', 50000);
        $this->seedCustomerPayment('payment-1', 50000, '2026-03-15');
        $this->seedPaymentAllocation('allocation-1', 'payment-1', 'note-1', 50000);

        $result = $this->buildHandler('refund-1')->handle('payment-1', 'note-1', 20000, '2026-03-16', '   ', 'owner-1');

        $this->assertTrue($result->isFailure());
        $this->assertSame(['refund' => ['AUDIT_REASON_REQUIRED']], $result->errors());
        $this->assertDatabaseCount('customer_refunds', 0);
        $this->assertDatabaseMissing('audit_logs', ['event' => 'customer_refund_recorded']);
    }

    public function test_record_customer_refund_handler_rejects_invalid_target(): void
    {
        $this->seedNote('note-1', 50000);

        $result = $this->buildHandler('refund-1')->handle('payment-missing', 'note-1', 20000, '2026-03-16', 'Alasan refund.', 'owner-1');

        $this->assertTrue($result->isFailure());
        $this->assertSame(['refund' => ['REFUND_INVALID_TARGET']], $result->errors());
        $this->assertDatabaseCount('customer_refunds', 0);
    }

    public function test_record_customer_refund_handler_rejects_when_refund_exceeds_allocated_pair(): void
    {
        $this->seedNote('note-1', 50000);
        $this->seedCustomerPayment('payment-1', 50000, '2026-03-15');
        $this->seedPaymentAllocation('allocation-1', 'payment-1', 'note-1', 30000);
        $this->seedCustomerRefund('refund-old', 'payment-1', 'note-1', 20000, '2026-03-16', 'Refund awal.');

        $result = $this->buildHandler('refund-1')->handle('payment-1', 'note-1', 15000, '2026-03-16', 'Refund tambahan.', 'owner-1');

        $this->assertTrue($result->isFailure());
        $this->assertSame(['refund' => ['REFUND_EXCEEDS_ALLOCATED_PAIR']], $result->errors());
        $this->assertDatabaseMissing('customer_refunds', ['id' => 'refund-1']);
    }

    private function buildHandler(string $refundId): RecordCustomerRefundHandler
    {
        return new RecordCustomerRefundHandler(
            new DatabaseCustomerPaymentReaderAdapter(),
            new DatabaseCustomerRefundReaderAdapter(),
            new DatabaseCustomerRefundWriterAdapter(),
            new DatabasePaymentAllocationReaderAdapter(),
            new DatabaseNoteReaderAdapter(),
            new class () implements TransactionManagerPort {
                public function begin(): void { DB::beginTransaction(); }
                public function commit(): void { DB::commit(); }
                public function rollBack(): void { DB::rollBack(); }
            },
            new class ($refundId) implements UuidPort {
                public function __construct(private readonly string $id) {}
                public function generate(): string { return $this->id; }
            },
            new DatabaseAuditLogAdapter(),
        );
    }

    private function seedNote(string $id, int $total): void
    {
        DB::table('notes')->insert(['id' => $id, 'customer_name' => 'Budi Santoso', 'transaction_date' => '2026-03-14', 'total_rupiah' => $total]);
    }

    private function seedCustomerPayment(string $id, int $amount, string $paidAt): void
    {
        DB::table('customer_payments')->insert(['id' => $id, 'amount_rupiah' => $amount, 'paid_at' => $paidAt]);
    }

    private function seedPaymentAllocation(string $id, string $paymentId, string $noteId, int $amount): void
    {
        DB::table('payment_allocations')->insert(['id' => $id, 'customer_payment_id' => $paymentId, 'note_id' => $noteId, 'amount_rupiah' => $amount]);
    }

    private function seedCustomerRefund(string $id, string $paymentId, string $noteId, int $amount, string $refundedAt, string $reason): void
    {
        DB::table('customer_refunds')->insert(['id' => $id, 'customer_payment_id' => $paymentId, 'note_id' => $noteId, 'amount_rupiah' => $amount, 'refunded_at' => $refundedAt, 'reason' => $reason]);
    }
}
