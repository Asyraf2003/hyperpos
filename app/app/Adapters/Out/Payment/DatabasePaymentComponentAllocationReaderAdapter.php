<?php

declare(strict_types=1);

namespace App\Adapters\Out\Payment;

use App\Core\Payment\PaymentComponentAllocation\PaymentComponentAllocation;
use App\Core\Shared\Exceptions\DomainException;
use App\Core\Shared\ValueObjects\Money;
use App\Ports\Out\Payment\PaymentComponentAllocationReaderPort;
use Illuminate\Support\Facades\DB;

final class DatabasePaymentComponentAllocationReaderAdapter implements PaymentComponentAllocationReaderPort
{
    public function getTotalAllocatedAmountByNoteId(string $noteId): Money
    {
        return Money::fromInt((int) DB::table('payment_component_allocations')
            ->where('note_id', $this->normalize($noteId, 'Note id pada payment component allocation wajib ada.'))
            ->sum('allocated_amount_rupiah'));
    }

    public function getTotalAllocatedAmountByCustomerPaymentIdAndNoteId(
        string $customerPaymentId,
        string $noteId,
    ): Money {
        return Money::fromInt((int) DB::table('payment_component_allocations')
            ->where('customer_payment_id', $this->normalize($customerPaymentId, 'Customer payment id wajib ada.'))
            ->where('note_id', $this->normalize($noteId, 'Note id pada payment component allocation wajib ada.'))
            ->sum('allocated_amount_rupiah'));
    }

    public function getTotalAllocatedAmountByWorkItemId(string $workItemId): Money
    {
        return Money::fromInt((int) DB::table('payment_component_allocations')
            ->where('work_item_id', $this->normalize($workItemId, 'Work item id pada payment component allocation wajib ada.'))
            ->sum('allocated_amount_rupiah'));
    }

    public function listByNoteId(string $noteId): array
    {
        return DB::table('payment_component_allocations')
            ->where('note_id', $this->normalize($noteId, 'Note id pada payment component allocation wajib ada.'))
            ->orderBy('allocation_priority')
            ->orderBy('id')
            ->get()
            ->map(static fn (object $row): PaymentComponentAllocation => PaymentComponentAllocation::rehydrate(
                (string) $row->id,
                (string) $row->customer_payment_id,
                (string) $row->note_id,
                (string) $row->work_item_id,
                (string) $row->component_type,
                (string) $row->component_ref_id,
                Money::fromInt((int) $row->component_amount_rupiah_snapshot),
                Money::fromInt((int) $row->allocated_amount_rupiah),
                (int) $row->allocation_priority,
            ))
            ->all();
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
