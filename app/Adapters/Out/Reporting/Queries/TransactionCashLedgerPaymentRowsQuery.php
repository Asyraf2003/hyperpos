<?php

declare(strict_types=1);

namespace App\Adapters\Out\Reporting\Queries;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

final class TransactionCashLedgerPaymentRowsQuery
{
    public function rows(string $fromEventDate, string $toEventDate): Collection
    {
        $componentRows = DB::table('payment_component_allocations')
            ->join('customer_payments', 'customer_payments.id', '=', 'payment_component_allocations.customer_payment_id')
            ->leftJoin('notes', 'notes.id', '=', 'payment_component_allocations.note_id')
            ->whereBetween('customer_payments.paid_at', [$fromEventDate, $toEventDate])
            ->groupBy(
                'payment_component_allocations.note_id',
                'notes.customer_name',
                'notes.transaction_date',
                'customer_payments.paid_at',
                'payment_component_allocations.customer_payment_id'
            )
            ->get([
                'payment_component_allocations.note_id',
                'notes.customer_name',
                'notes.transaction_date',
                'customer_payments.paid_at as event_date',
                DB::raw('SUM(payment_component_allocations.allocated_amount_rupiah) as event_amount_rupiah'),
                'payment_component_allocations.customer_payment_id',
            ]);

        if ($componentRows->isNotEmpty()) {
            return $componentRows->map(static fn (object $row): array => [
                'note_id' => (string) $row->note_id,
                'note_label' => trim((string) ($row->customer_name ?? '')) !== ''
                    ? (string) $row->customer_name . ' · ' . (string) ($row->transaction_date ?? $row->event_date)
                    : 'Nota ' . (string) ($row->transaction_date ?? $row->event_date),
                'event_date' => (string) $row->event_date,
                'event_type' => 'payment_allocation',
                'direction' => 'in',
                'event_amount_rupiah' => (int) $row->event_amount_rupiah,
                'customer_payment_id' => (string) $row->customer_payment_id,
                'refund_id' => null,
            ]);
        }

        return DB::table('payment_allocations')
            ->join('customer_payments', 'customer_payments.id', '=', 'payment_allocations.customer_payment_id')
            ->leftJoin('notes', 'notes.id', '=', 'payment_allocations.note_id')
            ->whereBetween('customer_payments.paid_at', [$fromEventDate, $toEventDate])
            ->get([
                'payment_allocations.note_id',
                'notes.customer_name',
                'notes.transaction_date',
                'customer_payments.paid_at as event_date',
                'payment_allocations.amount_rupiah as event_amount_rupiah',
                'payment_allocations.customer_payment_id',
            ])
            ->map(static fn (object $row): array => [
                'note_id' => (string) $row->note_id,
                'note_label' => trim((string) ($row->customer_name ?? '')) !== ''
                    ? (string) $row->customer_name . ' · ' . (string) ($row->transaction_date ?? $row->event_date)
                    : 'Nota ' . (string) ($row->transaction_date ?? $row->event_date),
                'event_date' => (string) $row->event_date,
                'event_type' => 'payment_allocation',
                'direction' => 'in',
                'event_amount_rupiah' => (int) $row->event_amount_rupiah,
                'customer_payment_id' => (string) $row->customer_payment_id,
                'refund_id' => null,
            ]);
    }
}
