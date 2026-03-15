<?php

declare(strict_types=1);

namespace App\Adapters\Out\Payment;

use App\Core\Shared\Exceptions\DomainException;
use App\Core\Shared\ValueObjects\Money;
use App\Ports\Out\Payment\PaymentAllocationReaderPort;
use Illuminate\Support\Facades\DB;

final class DatabasePaymentAllocationReaderAdapter implements PaymentAllocationReaderPort
{
    public function getTotalAllocatedAmountByNoteId(string $noteId): Money
    {
        $normalizedNoteId = $this->normalize($noteId, 'Note id pada payment allocation wajib ada.');

        $totalAllocated = (int) DB::table('payment_allocations')
            ->where('note_id', $normalizedNoteId)
            ->sum('amount_rupiah');

        return Money::fromInt($totalAllocated);
    }

    public function getTotalAllocatedAmountByCustomerPaymentIdAndNoteId(string $customerPaymentId, string $noteId): Money
    {
        $paymentId = $this->normalize($customerPaymentId, 'Customer payment id pada payment allocation wajib ada.');
        $normalizedNoteId = $this->normalize($noteId, 'Note id pada payment allocation wajib ada.');

        $totalAllocated = (int) DB::table('payment_allocations')
            ->where('customer_payment_id', $paymentId)
            ->where('note_id', $normalizedNoteId)
            ->sum('amount_rupiah');

        return Money::fromInt($totalAllocated);
    }

    private function normalize(string $value, string $message): string
    {
        $normalized = trim($value);
        if ($normalized === '') throw new DomainException($message);
        return $normalized;
    }
}
