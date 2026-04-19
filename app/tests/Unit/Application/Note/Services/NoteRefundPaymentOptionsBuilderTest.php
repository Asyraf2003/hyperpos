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
    public function test_it_builds_unique_refundable_payment_options_for_note(): void
    {
        $paymentComponents = $this->createMock(PaymentComponentAllocationReaderPort::class);
        $payments = $this->createMock(CustomerPaymentReaderPort::class);
        $allocations = $this->createMock(PaymentAllocationReaderPort::class);
        $refunds = $this->createMock(CustomerRefundReaderPort::class);

        $paymentComponents->method('listByNoteId')->with('note-1')->willReturn([
            PaymentComponentAllocation::rehydrate(
                'alloc-1',
                'payment-1',
                'note-1',
                'wi-1',
                'service_fee',
                'wi-1',
                Money::fromInt(30000),
                Money::fromInt(30000),
                1,
            ),
            PaymentComponentAllocation::rehydrate(
                'alloc-2',
                'payment-1',
                'note-1',
                'wi-2',
                'service_fee',
                'wi-2',
                Money::fromInt(20000),
                Money::fromInt(20000),
                2,
            ),
            PaymentComponentAllocation::rehydrate(
                'alloc-3',
                'payment-2',
                'note-1',
                'wi-3',
                'service_fee',
                'wi-3',
                Money::fromInt(10000),
                Money::fromInt(10000),
                3,
            ),
        ]);

        $payments->method('getById')->willReturnCallback(
            static fn (string $paymentId): ?CustomerPayment => match ($paymentId) {
                'payment-1' => CustomerPayment::rehydrate('payment-1', Money::fromInt(50000), new DateTimeImmutable('2026-04-15')),
                'payment-2' => CustomerPayment::rehydrate('payment-2', Money::fromInt(10000), new DateTimeImmutable('2026-04-16')),
                default => null,
            }
        );

        $allocations->method('getTotalAllocatedAmountByCustomerPaymentIdAndNoteId')->willReturnCallback(
            static fn (string $paymentId, string $noteId): Money => match ($paymentId . ':' . $noteId) {
                'payment-1:note-1' => Money::fromInt(50000),
                'payment-2:note-1' => Money::fromInt(10000),
                default => Money::zero(),
            }
        );

        $refunds->method('getTotalRefundedAmountByCustomerPaymentIdAndNoteId')->willReturnCallback(
            static fn (string $paymentId, string $noteId): Money => match ($paymentId . ':' . $noteId) {
                'payment-1:note-1' => Money::fromInt(10000),
                'payment-2:note-1' => Money::fromInt(10000),
                default => Money::zero(),
            }
        );

        $service = new NoteRefundPaymentOptionsBuilder(
            $paymentComponents,
            $payments,
            $allocations,
            $refunds,
        );

        $result = $service->build('note-1');

        $this->assertCount(1, $result);
        $this->assertSame('payment-1', $result[0]['value']);
        $this->assertSame('payment-1', $result[0]['payment_id']);
        $this->assertSame('2026-04-15', $result[0]['paid_at']);
        $this->assertSame(50000, $result[0]['allocated_rupiah']);
        $this->assertSame(10000, $result[0]['refunded_rupiah']);
        $this->assertSame(40000, $result[0]['refundable_rupiah']);
    }
}
