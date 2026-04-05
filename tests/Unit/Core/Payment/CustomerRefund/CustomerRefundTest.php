<?php

declare(strict_types=1);

namespace Tests\Unit\Core\Payment\CustomerRefund;

use App\Core\Payment\CustomerRefund\CustomerRefund;
use App\Core\Shared\Exceptions\DomainException;
use App\Core\Shared\ValueObjects\Money;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

final class CustomerRefundTest extends TestCase
{
    public function test_it_creates_customer_refund_with_valid_data(): void
    {
        $refund = CustomerRefund::create('refund-1', 'payment-1', 'note-1', Money::fromInt(50000), new DateTimeImmutable('2026-03-16'), 'Salah input total.');

        $this->assertSame('refund-1', $refund->id());
        $this->assertSame('payment-1', $refund->customerPaymentId());
        $this->assertSame('note-1', $refund->noteId());
        $this->assertSame(50000, $refund->amountRupiah()->amount());
        $this->assertSame('2026-03-16', $refund->refundedAt()->format('Y-m-d'));
        $this->assertSame('Salah input total.', $refund->reason());
    }

    public function test_it_rehydrates_customer_refund_with_valid_data(): void
    {
        $refund = CustomerRefund::rehydrate('refund-1', 'payment-1', 'note-1', Money::fromInt(25000), new DateTimeImmutable('2026-03-16'), 'Koreksi pembayaran.');

        $this->assertSame('refund-1', $refund->id());
        $this->assertSame('payment-1', $refund->customerPaymentId());
        $this->assertSame('note-1', $refund->noteId());
        $this->assertSame(25000, $refund->amountRupiah()->amount());
        $this->assertSame('2026-03-16', $refund->refundedAt()->format('Y-m-d'));
        $this->assertSame('Koreksi pembayaran.', $refund->reason());
    }

    public function test_it_rejects_blank_required_fields(): void
    {
        $this->expectException(DomainException::class);
        CustomerRefund::create('   ', 'payment-1', 'note-1', Money::fromInt(10000), new DateTimeImmutable('2026-03-16'), 'Alasan');
    }

    public function test_it_rejects_blank_payment_note_or_reason(): void
    {
        $cases = [
            ['refund-1', '   ', 'note-1', 'Alasan'],
            ['refund-1', 'payment-1', '   ', 'Alasan'],
            ['refund-1', 'payment-1', 'note-1', '   '],
        ];

        foreach ($cases as [$id, $paymentId, $noteId, $reason]) {
            try {
                CustomerRefund::create($id, $paymentId, $noteId, Money::fromInt(10000), new DateTimeImmutable('2026-03-16'), $reason);
                $this->fail('Expected DomainException was not thrown.');
            } catch (DomainException) {
                $this->addToAssertionCount(1);
            }
        }
    }

    public function test_it_rejects_zero_or_negative_amount(): void
    {
        $this->expectException(DomainException::class);
        CustomerRefund::create('refund-1', 'payment-1', 'note-1', Money::fromInt(0), new DateTimeImmutable('2026-03-16'), 'Alasan');
    }
}
