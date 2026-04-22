<?php

declare(strict_types=1);

namespace Tests\Unit\Application\Note\Services;

use App\Application\Note\Services\NoteRefundPaymentOptionsBuilder;
use App\Core\Payment\CustomerPayment\CustomerPayment;
use App\Core\Payment\PaymentComponentAllocation\PaymentComponentAllocation;
use App\Core\Shared\ValueObjects\Money;
use App\Ports\Out\Payment\CustomerPaymentReaderPort;
use App\Ports\Out\Payment\CustomerRefundReaderPort;
use App\Ports\Out\Payment\PaymentAllocationReaderPort;
use App\Ports\Out\Payment\PaymentComponentAllocationReaderPort;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

final class NoteRefundPaymentOptionsBuilderTest extends TestCase
{
    public function test_it_builds_refund_options_only_from_component_allocations(): void
    {
        $components = $this->createMock(PaymentComponentAllocationReaderPort::class);
        $payments = $this->createMock(CustomerPaymentReaderPort::class);
        $allocations = $this->createMock(PaymentAllocationReaderPort::class);
        $refunds = $this->createMock(CustomerRefundReaderPort::class);

        $components->method('listByNoteId')->with('note-1')->willReturn([
            PaymentComponentAllocation::rehydrate('a1', 'payment-1', 'note-1', 'wi-1', 'service_fee', 'wi-1', Money::fromInt(50000), Money::fromInt(30000), 1),
        ]);
        $payments->method('getById')->with('payment-1')->willReturn(CustomerPayment::rehydrate('payment-1', Money::fromInt(30000), new DateTimeImmutable('2026-04-22')));
        $allocations->method('getTotalAllocatedAmountByCustomerPaymentIdAndNoteId')->with('payment-1', 'note-1')->willReturn(Money::fromInt(30000));
        $refunds->method('getTotalRefundedAmountByCustomerPaymentIdAndNoteId')->with('payment-1', 'note-1')->willReturn(Money::fromInt(5000));

        $result = (new NoteRefundPaymentOptionsBuilder($components, $payments, $allocations, $refunds))->build('note-1');

        self::assertCount(1, $result);
        self::assertSame('payment-1', $result[0]['payment_id']);
        self::assertSame(25000, $result[0]['refundable_rupiah']);
    }

    public function test_it_returns_empty_when_only_legacy_allocations_exist_without_component_allocations(): void
    {
        $components = $this->createMock(PaymentComponentAllocationReaderPort::class);
        $payments = $this->createMock(CustomerPaymentReaderPort::class);
        $allocations = $this->createMock(PaymentAllocationReaderPort::class);
        $refunds = $this->createMock(CustomerRefundReaderPort::class);

        $components->method('listByNoteId')->with('note-legacy')->willReturn([]);

        $result = (new NoteRefundPaymentOptionsBuilder($components, $payments, $allocations, $refunds))->build('note-legacy');

        self::assertSame([], $result);
    }
}
