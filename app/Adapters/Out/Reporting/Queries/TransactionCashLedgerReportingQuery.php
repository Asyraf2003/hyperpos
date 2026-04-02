<?php

declare(strict_types=1);

namespace App\Adapters\Out\Reporting\Queries;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

final class TransactionCashLedgerReportingQuery
{
    public function rows(string $fromEventDate, string $toEventDate): array
    {
        $paymentRows = $this->paymentRows($fromEventDate, $toEventDate);
        $refundRows = $this->refundRows($fromEventDate, $toEventDate);

        return $paymentRows
            ->concat($refundRows)
            ->sortBy([['event_date', 'asc'], ['event_type', 'asc'], ['note_id', 'asc']])
            ->values()
            ->all();
    }

    public function reconciliation(string $fromEventDate, string $toEventDate): array
    {
        $rows = $this->rows($fromEventDate, $toEventDate);

        return [
            'total_in_rupiah' => array_sum(array_column(
                array_filter($rows, static fn (array $row): bool => $row['direction'] === 'in'),
                'event_amount_rupiah'
            )),
            'total_out_rupiah' => array_sum(array_column(
                array_filter($rows, static fn (array $row): bool => $row['direction'] === 'out'),
                'event_amount_rupiah'
            )),
        ];
    }

    private function paymentRows(string $fromEventDate, string $toEventDate): Collection
    {
        $componentRows = DB::table('payment_component_allocations')
            ->join('customer_payments', 'customer_payments.id', '=', 'payment_component_allocations.customer_payment_id')
            ->whereBetween('customer_payments.paid_at', [$fromEventDate, $toEventDate])
            ->groupBy('payment_component_allocations.note_id', 'customer_payments.paid_at', 'payment_component_allocations.customer_payment_id')
            ->get([
                'payment_component_allocations.note_id',
                'customer_payments.paid_at as event_date',
                DB::raw('SUM(payment_component_allocations.allocated_amount_rupiah) as event_amount_rupiah'),
                'payment_component_allocations.customer_payment_id',
            ]);

        if ($componentRows->isNotEmpty()) {
            return $componentRows->map(static fn (object $row): array => [
                'note_id' => (string) $row->note_id,
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
            ->whereBetween('customer_payments.paid_at', [$fromEventDate, $toEventDate])
            ->get([
                'payment_allocations.note_id',
                'customer_payments.paid_at as event_date',
                'payment_allocations.amount_rupiah as event_amount_rupiah',
                'payment_allocations.customer_payment_id',
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
    }

    private function refundRows(string $fromEventDate, string $toEventDate): Collection
    {
        $componentRows = DB::table('refund_component_allocations')
            ->join('customer_refunds', 'customer_refunds.id', '=', 'refund_component_allocations.customer_refund_id')
            ->whereBetween('customer_refunds.refunded_at', [$fromEventDate, $toEventDate])
            ->groupBy('refund_component_allocations.note_id', 'customer_refunds.refunded_at', 'refund_component_allocations.customer_refund_id')
            ->get([
                'refund_component_allocations.note_id',
                'customer_refunds.refunded_at as event_date',
                DB::raw('SUM(refund_component_allocations.refunded_amount_rupiah) as event_amount_rupiah'),
                'refund_component_allocations.customer_refund_id as refund_id',
            ]);

        if ($componentRows->isNotEmpty()) {
            return $componentRows->map(static fn (object $row): array => [
                'note_id' => (string) $row->note_id,
                'event_date' => (string) $row->event_date,
                'event_type' => 'refund',
                'direction' => 'out',
                'event_amount_rupiah' => (int) $row->event_amount_rupiah,
                'customer_payment_id' => null,
                'refund_id' => (string) $row->refund_id,
            ]);
        }

        return DB::table('customer_refunds')
            ->whereBetween('customer_refunds.refunded_at', [$fromEventDate, $toEventDate])
            ->get([
                'customer_refunds.note_id',
                'customer_refunds.refunded_at as event_date',
                'customer_refunds.amount_rupiah as event_amount_rupiah',
                'customer_refunds.id as refund_id',
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
    }
}
