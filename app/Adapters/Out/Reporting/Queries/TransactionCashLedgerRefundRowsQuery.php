<?php

declare(strict_types=1);

namespace App\Adapters\Out\Reporting\Queries;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

final class TransactionCashLedgerRefundRowsQuery
{
    public function rows(string $fromEventDate, string $toEventDate): Collection
    {
        $componentRows = DB::table('refund_component_allocations')
            ->join('customer_refunds', 'customer_refunds.id', '=', 'refund_component_allocations.customer_refund_id')
            ->whereBetween('customer_refunds.refunded_at', [$fromEventDate, $toEventDate])
            ->groupBy(
                'refund_component_allocations.note_id',
                'customer_refunds.refunded_at',
                'refund_component_allocations.customer_refund_id'
            )
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
