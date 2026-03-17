<?php

declare(strict_types=1);

namespace App\Adapters\Out\Reporting;

use App\Ports\Out\Reporting\TransactionReportingSourceReaderPort;
use Illuminate\Support\Facades\DB;

final class DatabaseTransactionReportingSourceReaderAdapter implements TransactionReportingSourceReaderPort
{
    public function getTransactionSummaryPerNoteRows(
        string $fromTransactionDate,
        string $toTransactionDate,
    ): array {
        $allocationSubquery = DB::table('payment_allocations')
            ->selectRaw('note_id, SUM(amount_rupiah) as allocated_payment_rupiah')
            ->groupBy('note_id');

        $refundSubquery = DB::table('customer_refunds')
            ->selectRaw('note_id, SUM(amount_rupiah) as refunded_rupiah')
            ->groupBy('note_id');

        return DB::table('notes')
            ->leftJoinSub($allocationSubquery, 'allocation_totals', function ($join): void {
                $join->on('allocation_totals.note_id', '=', 'notes.id');
            })
            ->leftJoinSub($refundSubquery, 'refund_totals', function ($join): void {
                $join->on('refund_totals.note_id', '=', 'notes.id');
            })
            ->whereBetween('notes.transaction_date', [$fromTransactionDate, $toTransactionDate])
            ->orderBy('notes.transaction_date')
            ->orderBy('notes.id')
            ->get([
                'notes.id as note_id',
                'notes.transaction_date',
                'notes.customer_name',
                'notes.total_rupiah as gross_transaction_rupiah',
                DB::raw('COALESCE(allocation_totals.allocated_payment_rupiah, 0) as allocated_payment_rupiah'),
                DB::raw('COALESCE(refund_totals.refunded_rupiah, 0) as refunded_rupiah'),
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

    public function getTransactionSummaryPerNoteReconciliation(
        string $fromTransactionDate,
        string $toTransactionDate,
    ): array {
        $filteredNotesSubquery = DB::table('notes')
            ->select('id', 'total_rupiah')
            ->whereBetween('transaction_date', [$fromTransactionDate, $toTransactionDate]);

        $grossTotals = DB::query()
            ->fromSub($filteredNotesSubquery, 'filtered_notes')
            ->selectRaw('COUNT(*) as total_notes, COALESCE(SUM(total_rupiah), 0) as gross_transaction_rupiah')
            ->first();

        $allocationTotals = DB::table('payment_allocations')
            ->joinSub($filteredNotesSubquery, 'filtered_notes', function ($join): void {
                $join->on('filtered_notes.id', '=', 'payment_allocations.note_id');
            })
            ->selectRaw('COALESCE(SUM(payment_allocations.amount_rupiah), 0) as allocated_payment_rupiah')
            ->first();

        $refundTotals = DB::table('customer_refunds')
            ->joinSub($filteredNotesSubquery, 'filtered_notes', function ($join): void {
                $join->on('filtered_notes.id', '=', 'customer_refunds.note_id');
            })
            ->selectRaw('COALESCE(SUM(customer_refunds.amount_rupiah), 0) as refunded_rupiah')
            ->first();

        return [
            'total_notes' => (int) ($grossTotals->total_notes ?? 0),
            'gross_transaction_rupiah' => (int) ($grossTotals->gross_transaction_rupiah ?? 0),
            'allocated_payment_rupiah' => (int) ($allocationTotals->allocated_payment_rupiah ?? 0),
            'refunded_rupiah' => (int) ($refundTotals->refunded_rupiah ?? 0),
        ];
    }

    public function getTransactionCashLedgerPerNoteRows(
        string $fromEventDate,
        string $toEventDate,
    ): array {
        return [];
    }

    public function getTransactionCashLedgerPerNoteReconciliation(
        string $fromEventDate,
        string $toEventDate,
    ): array {
        return [
            'total_in_rupiah' => 0,
            'total_out_rupiah' => 0,
        ];
    }
}
