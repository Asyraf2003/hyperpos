<?php

declare(strict_types=1);

namespace App\Adapters\Out\Reporting\Queries;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

final class TransactionSummaryCashPaymentTotalsQuery
{
    public function query(): Builder
    {
        return DB::query()
            ->fromSub(
                $this->paymentAllocationRows()
                    ->unionAll($this->componentAllocationRows())
                    ->unionAll($this->refundedPaymentFallbackRows()),
                'cash_payment_rows'
            )
            ->selectRaw('note_id, SUM(amount_rupiah) as allocated_payment_rupiah')
            ->groupBy('note_id');
    }

    private function paymentAllocationRows(): Builder
    {
        return DB::table('payment_allocations')
            ->selectRaw('payment_allocations.note_id, SUM(payment_allocations.amount_rupiah) as amount_rupiah')
            ->groupBy('payment_allocations.note_id');
    }

    private function componentAllocationRows(): Builder
    {
        return DB::table('payment_component_allocations')
            ->whereNotExists(static function ($query): void {
                $query->selectRaw('1')
                    ->from('payment_allocations')
                    ->whereColumn('payment_allocations.customer_payment_id', 'payment_component_allocations.customer_payment_id')
                    ->whereColumn('payment_allocations.note_id', 'payment_component_allocations.note_id');
            })
            ->selectRaw('payment_component_allocations.note_id, SUM(payment_component_allocations.allocated_amount_rupiah) as amount_rupiah')
            ->groupBy('payment_component_allocations.note_id');
    }

    private function refundedPaymentFallbackRows(): Builder
    {
        return DB::table('customer_refunds')
            ->join('customer_payments', 'customer_payments.id', '=', 'customer_refunds.customer_payment_id')
            ->whereNotExists(static function ($query): void {
                $query->selectRaw('1')
                    ->from('payment_allocations')
                    ->whereColumn('payment_allocations.customer_payment_id', 'customer_refunds.customer_payment_id')
                    ->whereColumn('payment_allocations.note_id', 'customer_refunds.note_id');
            })
            ->whereNotExists(static function ($query): void {
                $query->selectRaw('1')
                    ->from('payment_component_allocations')
                    ->whereColumn('payment_component_allocations.customer_payment_id', 'customer_refunds.customer_payment_id')
                    ->whereColumn('payment_component_allocations.note_id', 'customer_refunds.note_id');
            })
            ->selectRaw('customer_refunds.note_id, MAX(customer_payments.amount_rupiah) as amount_rupiah')
            ->groupBy('customer_refunds.note_id', 'customer_refunds.customer_payment_id');
    }
}
