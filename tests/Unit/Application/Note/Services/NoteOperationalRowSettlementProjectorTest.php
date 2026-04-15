<?php

declare(strict_types=1);

namespace Tests\Unit\Application\Note\Services;

use App\Application\Note\Services\NoteOperationalRowSettlementProjector;
use App\Core\Note\WorkItem\ServiceDetail;
use App\Core\Note\WorkItem\WorkItem;
use App\Core\Shared\ValueObjects\Money;
use App\Ports\Out\Payment\CustomerRefundReaderPort;
use App\Ports\Out\Payment\PaymentAllocationReaderPort;
use PHPUnit\Framework\TestCase;

final class NoteOperationalRowSettlementProjectorTest extends TestCase
{
    public function test_it_projects_allocated_amount_across_current_rows_by_line_order(): void
    {
        $allocations = $this->createMock(PaymentAllocationReaderPort::class);
        $refunds = $this->createMock(CustomerRefundReaderPort::class);

        $allocations->method('getTotalAllocatedAmountByNoteId')->with('note-1')->willReturn(Money::fromInt(25000));
        $refunds->method('getTotalRefundedAmountByNoteId')->with('note-1')->willReturn(Money::zero());

        $projector = new NoteOperationalRowSettlementProjector($allocations, $refunds);

        $rows = [
            $this->makeServiceRow('row-2', 'note-1', 2, 15000),
            $this->makeServiceRow('row-1', 'note-1', 1, 10000),
            $this->makeServiceRow('row-3', 'note-1', 3, 20000),
        ];

        $result = $projector->build('note-1', $rows);

        $this->assertSame(10000, $result['row-1']['allocated_rupiah']);
        $this->assertSame(10000, $result['row-1']['net_paid_rupiah']);
        $this->assertSame(0, $result['row-1']['outstanding_rupiah']);
        $this->assertSame('lunas', $result['row-1']['settlement_label']);

        $this->assertSame(15000, $result['row-2']['allocated_rupiah']);
        $this->assertSame(15000, $result['row-2']['net_paid_rupiah']);
        $this->assertSame(0, $result['row-2']['outstanding_rupiah']);
        $this->assertSame('lunas', $result['row-2']['settlement_label']);

        $this->assertSame(0, $result['row-3']['allocated_rupiah']);
        $this->assertSame(0, $result['row-3']['net_paid_rupiah']);
        $this->assertSame(20000, $result['row-3']['outstanding_rupiah']);
        $this->assertSame('hutang', $result['row-3']['settlement_label']);
    }

    public function test_it_projects_refund_against_current_rows_without_mutating_history(): void
    {
        $allocations = $this->createMock(PaymentAllocationReaderPort::class);
        $refunds = $this->createMock(CustomerRefundReaderPort::class);

        $allocations->method('getTotalAllocatedAmountByNoteId')->with('note-1')->willReturn(Money::fromInt(30000));
        $refunds->method('getTotalRefundedAmountByNoteId')->with('note-1')->willReturn(Money::fromInt(5000));

        $projector = new NoteOperationalRowSettlementProjector($allocations, $refunds);

        $rows = [
            $this->makeServiceRow('row-1', 'note-1', 1, 10000),
            $this->makeServiceRow('row-2', 'note-1', 2, 10000),
            $this->makeServiceRow('row-3', 'note-1', 3, 10000),
        ];

        $result = $projector->build('note-1', $rows);

        $this->assertSame(10000, $result['row-1']['allocated_rupiah']);
        $this->assertSame(5000, $result['row-1']['refunded_rupiah']);
        $this->assertSame(5000, $result['row-1']['net_paid_rupiah']);
        $this->assertSame(5000, $result['row-1']['outstanding_rupiah']);
        $this->assertSame('dp', $result['row-1']['settlement_label']);

        $this->assertSame(10000, $result['row-2']['allocated_rupiah']);
        $this->assertSame(0, $result['row-2']['refunded_rupiah']);
        $this->assertSame(10000, $result['row-2']['net_paid_rupiah']);
        $this->assertSame(0, $result['row-2']['outstanding_rupiah']);
        $this->assertSame('lunas', $result['row-2']['settlement_label']);

        $this->assertSame(10000, $result['row-3']['allocated_rupiah']);
        $this->assertSame(0, $result['row-3']['refunded_rupiah']);
        $this->assertSame(10000, $result['row-3']['net_paid_rupiah']);
        $this->assertSame(0, $result['row-3']['outstanding_rupiah']);
        $this->assertSame('lunas', $result['row-3']['settlement_label']);
    }

    private function makeServiceRow(string $id, string $noteId, int $lineNo, int $price): WorkItem
    {
        return WorkItem::createServiceOnly(
            $id,
            $noteId,
            $lineNo,
            ServiceDetail::create(
                'Servis ' . $lineNo,
                Money::fromInt($price),
                ServiceDetail::PART_SOURCE_NONE,
            ),
        );
    }
}
