<?php

declare(strict_types=1);

namespace App\Adapters\Out\Reporting\Queries;

use Illuminate\Support\Facades\DB;

final class TransactionCashLedgerReportingQuery
{
    public function rows(string $fromEventDate, string $toEventDate): array
    {
        $paymentRows = DB::table('payment_component_allocations')
            ->join('customer_payments', 'customer_payments.id', '=', 'payment_component_allocations.customer_payment_id')
            ->whereBetween('customer_payments.paid_at', [$fromEventDate, $toEventDate])
            ->groupBy('payment_component_allocations.note_id', 'customer_payments.paid_at', 'payment_component_allocations.customer_payment_id')
            ->orderBy('customer_payments.paid_at')
            ->orderBy('payment_component_allocations.note_id')
            ->get([
                'payment_component_allocations.note_id',
                'customer_payments.paid_at as event_date',
                DB::raw('SUM(payment_component_allocations.allocated_amount_rupiah) as event_amount_rupiah'),
                'payment_component_allocations.customer_payment_id',
            ])
            ->map(static fn (object $row): array => [
                'note_id' => (string) $row->note_id,
                'event_date' => (string) $row->event_date,
                'event_type' => 'payment_allocation',
                'direction' => 'in',
                'event_amount_rupiah' => (int) $row->event_amount_rupiah,
                'customer_payment_id' => (string) $row->customer_payment_id,
                'refund_id' => null,
            ]);

        $refundRows = DB::table('refund_component_allocations')
            ->join('customer_refunds', 'customer_refunds.id', '=', 'refund_component_allocations.customer_refund_id')
            ->whereBetween('customer_refunds.refunded_at', [$fromEventDate, $toEventDate])
            ->groupBy('refund_component_allocations.note_id', 'customer_refunds.refunded_at', 'refund_component_allocations.customer_refund_id')
            ->orderBy('customer_refunds.refunded_at')
            ->orderBy('refund_component_allocations.note_id')
            ->get([
                'refund_component_allocations.note_id',
                'customer_refunds.refunded_at as event_date',
                DB::raw('SUM(refund_component_allocations.refunded_amount_rupiah) as event_amount_rupiah'),
                'refund_component_allocations.customer_refund_id as refund_id',
            ])
            ->map(static fn (object $row): array => [
                'note_id' => (string) $row->note_id,
                'event_date' => (string) $row->event_date,
                'event_type' => 'refund',
                'direction' => 'out',
                'event_amount_rupiah' => (int) $row->event_amount_rupiah,
                'customer_payment_id' => null,
                'refund_id' => (string) $row->refund_id,
            ]);

        return $paymentRows
            ->concat($refundRows)
            ->sortBy([['event_date', 'asc'], ['event_type', 'asc'], ['note_id', 'asc']])
            ->values()
            ->all();
    }

    public function reconciliation(string $fromEventDate, string $toEventDate): array
    {
        $paymentTotals = DB::table('payment_component_allocations')
            ->join('customer_payments', 'customer_payments.id', '=', 'payment_component_allocations.customer_payment_id')
            ->whereBetween('customer_payments.paid_at', [$fromEventDate, $toEventDate])
            ->selectRaw('COALESCE(SUM(payment_component_allocations.allocated_amount_rupiah), 0) as total_in_rupiah')
            ->first();

        $refundTotals = DB::table('refund_component_allocations')
            ->join('customer_refunds', 'customer_refunds.id', '=', 'refund_component_allocations.customer_refund_id')
            ->whereBetween('customer_refunds.refunded_at', [$fromEventDate, $toEventDate])
            ->selectRaw('COALESCE(SUM(refund_component_allocations.refunded_amount_rupiah), 0) as total_out_rupiah')
            ->first();

        return [
            'total_in_rupiah' => (int) ($paymentTotals->total_in_rupiah ?? 0),
            'total_out_rupiah' => (int) ($refundTotals->total_out_rupiah ?? 0),
        ];
    }
}
