<?php

declare(strict_types=1);

namespace Tests\Unit\Application\Note\Services;

use App\Application\Note\Services\NoteOperationalComponentAllocationTotalsGrouper;
use App\Application\Note\Services\NoteOperationalComponentSettlementSummaryBuilder;
use App\Application\Note\Services\NoteOperationalLegacySettlementSummaryBuilder;
use App\Application\Note\Services\NoteOperationalRowSettlementProjector;
use App\Application\Note\Services\NoteOperationalSettlementLabelResolver;
use App\Core\Note\WorkItem\ServiceDetail;
use App\Core\Note\WorkItem\WorkItem;
use App\Core\Payment\PaymentComponentAllocation\PaymentComponentAllocation;
use App\Core\Payment\RefundComponentAllocation\RefundComponentAllocation;
use App\Core\Shared\ValueObjects\Money;
use App\Ports\Out\Payment\CustomerRefundReaderPort;
use App\Ports\Out\Payment\PaymentAllocationReaderPort;
use App\Ports\Out\Payment\PaymentComponentAllocationReaderPort;
use App\Ports\Out\Payment\RefundComponentAllocationReaderPort;
use PHPUnit\Framework\TestCase;

final class NoteOperationalRowSettlementProjectorTest extends TestCase
{
    public function test_it_projects_allocations_by_actual_work_item_not_by_line_order(): void
    {
        $componentPayments = $this->createMock(PaymentComponentAllocationReaderPort::class);
        $componentRefunds = $this->createMock(RefundComponentAllocationReaderPort::class);
        $legacyPayments = $this->createMock(PaymentAllocationReaderPort::class);
        $legacyRefunds = $this->createMock(CustomerRefundReaderPort::class);

        $componentPayments->method('listByNoteId')->with('note-1')->willReturn([
            PaymentComponentAllocation::rehydrate(
                'pa-1',
                'payment-1',
                'note-1',
                'row-2',
                'service_fee',
                'row-2',
                Money::fromInt(15000),
                Money::fromInt(15000),
                1,
            ),
            PaymentComponentAllocation::rehydrate(
                'pa-2',
                'payment-1',
                'note-1',
                'row-1',
                'service_fee',
                'row-1',
                Money::fromInt(10000),
                Money::fromInt(10000),
                2,
            ),
        ]);

        $componentRefunds->method('listByNoteId')->with('note-1')->willReturn([]);

        $projector = $this->makeProjector(
            $componentPayments,
            $componentRefunds,
            $legacyPayments,
            $legacyRefunds,
        );

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

    public function test_it_projects_refund_by_actual_work_item_not_by_note_remainder(): void
    {
        $componentPayments = $this->createMock(PaymentComponentAllocationReaderPort::class);
        $componentRefunds = $this->createMock(RefundComponentAllocationReaderPort::class);
        $legacyPayments = $this->createMock(PaymentAllocationReaderPort::class);
        $legacyRefunds = $this->createMock(CustomerRefundReaderPort::class);

        $componentPayments->method('listByNoteId')->with('note-1')->willReturn([
            PaymentComponentAllocation::rehydrate(
                'pa-1',
                'payment-1',
                'note-1',
                'row-2',
                'service_fee',
                'row-2',
                Money::fromInt(10000),
                Money::fromInt(10000),
                1,
            ),
            PaymentComponentAllocation::rehydrate(
                'pa-2',
                'payment-1',
                'note-1',
                'row-3',
                'service_fee',
                'row-3',
                Money::fromInt(10000),
                Money::fromInt(10000),
                2,
            ),
        ]);

        $componentRefunds->method('listByNoteId')->with('note-1')->willReturn([
            RefundComponentAllocation::rehydrate(
                'ra-1',
                'refund-1',
                'payment-1',
                'note-1',
                'row-2',
                'service_fee',
                'row-2',
                Money::fromInt(5000),
                1,
            ),
        ]);

        $projector = $this->makeProjector(
            $componentPayments,
            $componentRefunds,
            $legacyPayments,
            $legacyRefunds,
        );

        $rows = [
            $this->makeServiceRow('row-1', 'note-1', 1, 10000),
            $this->makeServiceRow('row-2', 'note-1', 2, 10000),
            $this->makeServiceRow('row-3', 'note-1', 3, 10000),
        ];

        $result = $projector->build('note-1', $rows);

        $this->assertSame(0, $result['row-1']['allocated_rupiah']);
        $this->assertSame(0, $result['row-1']['refunded_rupiah']);
        $this->assertSame(0, $result['row-1']['net_paid_rupiah']);
        $this->assertSame(10000, $result['row-1']['outstanding_rupiah']);
        $this->assertSame('hutang', $result['row-1']['settlement_label']);

        $this->assertSame(10000, $result['row-2']['allocated_rupiah']);
        $this->assertSame(5000, $result['row-2']['refunded_rupiah']);
        $this->assertSame(5000, $result['row-2']['net_paid_rupiah']);
        $this->assertSame(5000, $result['row-2']['outstanding_rupiah']);
        $this->assertSame('dp', $result['row-2']['settlement_label']);

        $this->assertSame(10000, $result['row-3']['allocated_rupiah']);
        $this->assertSame(0, $result['row-3']['refunded_rupiah']);
        $this->assertSame(10000, $result['row-3']['net_paid_rupiah']);
        $this->assertSame(0, $result['row-3']['outstanding_rupiah']);
        $this->assertSame('lunas', $result['row-3']['settlement_label']);
    }

    public function test_it_falls_back_to_legacy_note_totals_when_component_allocations_are_missing(): void
    {
        $componentPayments = $this->createMock(PaymentComponentAllocationReaderPort::class);
        $componentRefunds = $this->createMock(RefundComponentAllocationReaderPort::class);
        $legacyPayments = $this->createMock(PaymentAllocationReaderPort::class);
        $legacyRefunds = $this->createMock(CustomerRefundReaderPort::class);

        $componentPayments->method('listByNoteId')->with('note-legacy')->willReturn([]);
        $componentRefunds->method('listByNoteId')->with('note-legacy')->willReturn([]);

        $legacyPayments->method('getTotalAllocatedAmountByNoteId')
            ->with('note-legacy')
            ->willReturn(Money::fromInt(25000));

        $legacyRefunds->method('getTotalRefundedAmountByNoteId')
            ->with('note-legacy')
            ->willReturn(Money::fromInt(0));

        $projector = $this->makeProjector(
            $componentPayments,
            $componentRefunds,
            $legacyPayments,
            $legacyRefunds,
        );

        $rows = [
            $this->makeServiceRow('row-1', 'note-legacy', 1, 10000),
            $this->makeServiceRow('row-2', 'note-legacy', 2, 15000),
            $this->makeServiceRow('row-3', 'note-legacy', 3, 5000),
        ];

        $result = $projector->build('note-legacy', $rows);

        $this->assertSame(10000, $result['row-1']['allocated_rupiah']);
        $this->assertSame(0, $result['row-1']['outstanding_rupiah']);
        $this->assertSame('lunas', $result['row-1']['settlement_label']);

        $this->assertSame(15000, $result['row-2']['allocated_rupiah']);
        $this->assertSame(0, $result['row-2']['outstanding_rupiah']);
        $this->assertSame('lunas', $result['row-2']['settlement_label']);

        $this->assertSame(0, $result['row-3']['allocated_rupiah']);
        $this->assertSame(5000, $result['row-3']['outstanding_rupiah']);
        $this->assertSame('hutang', $result['row-3']['settlement_label']);
    }

    private function makeProjector(
        PaymentComponentAllocationReaderPort $componentPayments,
        RefundComponentAllocationReaderPort $componentRefunds,
        PaymentAllocationReaderPort $legacyPayments,
        CustomerRefundReaderPort $legacyRefunds,
    ): NoteOperationalRowSettlementProjector {
        $labels = new NoteOperationalSettlementLabelResolver();

        return new NoteOperationalRowSettlementProjector(
            $componentPayments,
            $componentRefunds,
            $legacyPayments,
            $legacyRefunds,
            new NoteOperationalComponentAllocationTotalsGrouper(),
            new NoteOperationalComponentSettlementSummaryBuilder($labels),
            new NoteOperationalLegacySettlementSummaryBuilder($labels),
        );
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
