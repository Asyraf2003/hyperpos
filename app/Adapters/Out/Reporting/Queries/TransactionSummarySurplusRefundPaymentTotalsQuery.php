<?php

declare(strict_types=1);

namespace App\Adapters\Out\Reporting\Queries;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

final class TransactionSummarySurplusRefundPaymentTotalsQuery
{
    public function query(): Builder
    {
        return DB::table('note_revision_surplus_refund_payments')
            ->selectRaw('note_root_id as note_id, SUM(amount_rupiah) as surplus_refund_paid_rupiah')
            ->where('status', 'active')
            ->groupBy('note_root_id');
    }
}
