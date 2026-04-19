<?php

declare(strict_types=1);

namespace App\Adapters\Out\Payment;

use App\Core\Payment\RefundComponentAllocation\RefundComponentAllocation;
use App\Core\Shared\Exceptions\DomainException;
use App\Core\Shared\ValueObjects\Money;
use App\Ports\Out\Payment\RefundComponentAllocationReaderPort;
use Illuminate\Support\Facades\DB;

final class DatabaseRefundComponentAllocationReaderAdapter implements RefundComponentAllocationReaderPort
{
    public function getTotalRefundedAmountByNoteId(string $noteId): Money
    {
        return Money::fromInt((int) DB::table('refund_component_allocations')
            ->where('note_id', $this->normalize($noteId, 'Note id pada refund component allocation wajib ada.'))
            ->sum('refunded_amount_rupiah'));
    }

    public function getTotalRefundedAmountByCustomerPaymentIdAndNoteId(
        string $customerPaymentId,
        string $noteId,
    ): Money {
        return Money::fromInt((int) DB::table('refund_component_allocations')
            ->where('customer_payment_id', $this->normalize($customerPaymentId, 'Customer payment id wajib ada.'))
            ->where('note_id', $this->normalize($noteId, 'Note id pada refund component allocation wajib ada.'))
            ->sum('refunded_amount_rupiah'));
    }

    public function getTotalRefundedAmountByWorkItemId(string $workItemId): Money
    {
        return Money::fromInt((int) DB::table('refund_component_allocations')
            ->where('work_item_id', $this->normalize($workItemId, 'Work item id pada refund component allocation wajib ada.'))
            ->sum('refunded_amount_rupiah'));
    }

    public function listByNoteId(string $noteId): array
    {
        return DB::table('refund_component_allocations')
            ->where('note_id', $this->normalize($noteId, 'Note id pada refund component allocation wajib ada.'))
            ->orderBy('refund_priority')
            ->orderBy('id')
            ->get()
            ->map(static fn (object $row): RefundComponentAllocation => RefundComponentAllocation::rehydrate(
                (string) $row->id,
                (string) $row->customer_refund_id,
                (string) $row->customer_payment_id,
                (string) $row->note_id,
                (string) $row->work_item_id,
                (string) $row->component_type,
                (string) $row->component_ref_id,
                Money::fromInt((int) $row->refunded_amount_rupiah),
                (int) $row->refund_priority,
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
