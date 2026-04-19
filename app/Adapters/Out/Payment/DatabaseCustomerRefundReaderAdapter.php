<?php

declare(strict_types=1);

namespace App\Adapters\Out\Payment;

use App\Core\Shared\Exceptions\DomainException;
use App\Core\Shared\ValueObjects\Money;
use App\Ports\Out\Payment\CustomerRefundReaderPort;
use Illuminate\Support\Facades\DB;

final class DatabaseCustomerRefundReaderAdapter implements CustomerRefundReaderPort
{
    public function getTotalRefundedAmountByNoteId(string $noteId): Money
    {
        $normalizedNoteId = $this->normalize($noteId, 'Note id pada customer refund wajib ada.');
        $componentTotal = (int) DB::table('refund_component_allocations')
            ->where('note_id', $normalizedNoteId)
            ->sum('refunded_amount_rupiah');

        if ($componentTotal > 0) {
            return Money::fromInt($componentTotal);
        }

        return Money::fromInt((int) DB::table('customer_refunds')
            ->where('note_id', $normalizedNoteId)
            ->sum('amount_rupiah'));
    }

    public function getTotalRefundedAmountByCustomerPaymentIdAndNoteId(string $customerPaymentId, string $noteId): Money
    {
        $paymentId = $this->normalize($customerPaymentId, 'Customer payment id pada customer refund wajib ada.');
        $normalizedNoteId = $this->normalize($noteId, 'Note id pada customer refund wajib ada.');
        $componentTotal = (int) DB::table('refund_component_allocations')
            ->where('customer_payment_id', $paymentId)
            ->where('note_id', $normalizedNoteId)
            ->sum('refunded_amount_rupiah');

        if ($componentTotal > 0) {
            return Money::fromInt($componentTotal);
        }

        return Money::fromInt((int) DB::table('customer_refunds')
            ->where('customer_payment_id', $paymentId)
            ->where('note_id', $normalizedNoteId)
            ->sum('amount_rupiah'));
    }

    private function normalize(string $value, string $message): string
    {
        $normalized = trim($value);

        if ($normalized === '') {
            throw new DomainException($message);
        }

        return $normalized;
    }
}
