<?php

declare(strict_types=1);

namespace App\Adapters\Out\Reporting\Queries;

use Illuminate\Support\Facades\DB;

final class TransactionSummaryReportingQuery
{
    public function rows(string $fromTransactionDate, string $toTransactionDate): array
    {
        $componentAllocationTotals = DB::table('payment_component_allocations')
            ->selectRaw('note_id, SUM(allocated_amount_rupiah) as allocated_payment_rupiah')
            ->groupBy('note_id');

        $legacyAllocationTotals = DB::table('payment_allocations')
            ->selectRaw('note_id, SUM(amount_rupiah) as allocated_payment_rupiah')
            ->groupBy('note_id');

        $componentRefundTotals = DB::table('refund_component_allocations')
            ->selectRaw('note_id, SUM(refunded_amount_rupiah) as refunded_rupiah')
            ->groupBy('note_id');

        $legacyRefundTotals = DB::table('customer_refunds')
            ->selectRaw('note_id, SUM(amount_rupiah) as refunded_rupiah')
            ->groupBy('note_id');

        return DB::table('notes')
            ->leftJoinSub($componentAllocationTotals, 'component_allocation_totals', fn ($join) => $join->on('component_allocation_totals.note_id', '=', 'notes.id'))
            ->leftJoinSub($legacyAllocationTotals, 'legacy_allocation_totals', fn ($join) => $join->on('legacy_allocation_totals.note_id', '=', 'notes.id'))
            ->leftJoinSub($componentRefundTotals, 'component_refund_totals', fn ($join) => $join->on('component_refund_totals.note_id', '=', 'notes.id'))
            ->leftJoinSub($legacyRefundTotals, 'legacy_refund_totals', fn ($join) => $join->on('legacy_refund_totals.note_id', '=', 'notes.id'))
            ->whereBetween('notes.transaction_date', [$fromTransactionDate, $toTransactionDate])
            ->orderBy('notes.transaction_date')
            ->orderBy('notes.id')
            ->get([
                'notes.id as note_id',
                'notes.transaction_date',
                'notes.customer_name',
                'notes.total_rupiah as gross_transaction_rupiah',
                DB::raw('COALESCE(component_allocation_totals.allocated_payment_rupiah, legacy_allocation_totals.allocated_payment_rupiah, 0) as allocated_payment_rupiah'),
                DB::raw('COALESCE(component_refund_totals.refunded_rupiah, legacy_refund_totals.refunded_rupiah, 0) as refunded_rupiah'),
            ])
            ->map(static fn (object $row): array => [
                'note_id' => (string) $row->note_id,
                'transaction_date' => (string) $row->transaction_date,
                'customer_name' => (string) $row->customer_name,
                'gross_transaction_rupiah' => (int) $row->gross_transaction_rupiah,
                'allocated_payment_rupiah' => (int) $row->allocated_payment_rupiah,
                'refunded_rupiah' => (int) $row->refunded_rupiah,
            ])
            ->all();
    }

    public function reconciliation(string $fromTransactionDate, string $toTransactionDate): array
    {
        $rows = $this->rows($fromTransactionDate, $toTransactionDate);

        return [
            'total_notes' => count($rows),
            'gross_transaction_rupiah' => array_sum(array_column($rows, 'gross_transaction_rupiah')),
            'allocated_payment_rupiah' => array_sum(array_column($rows, 'allocated_payment_rupiah')),
            'refunded_rupiah' => array_sum(array_column($rows, 'refunded_rupiah')),
        ];
    }
}
