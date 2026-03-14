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
        $normalizedNoteId = trim($noteId);

        if ($normalizedNoteId === '') {
            throw new DomainException('Note id pada payment allocation wajib ada.');
        }

        $totalAllocated = (int) DB::table('payment_allocations')
            ->where('note_id', $normalizedNoteId)
            ->sum('amount_rupiah');

        return Money::fromInt($totalAllocated);
    }
}
