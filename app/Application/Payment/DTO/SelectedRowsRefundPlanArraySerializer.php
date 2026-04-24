<?php

declare(strict_types=1);

namespace App\Application\Payment\DTO;

final class SelectedRowsRefundPlanArraySerializer
{
    /**
     * @return array{
     *   note_id: string,
     *   selected_row_ids: list<string>,
     *   unpaid_row_ids: list<string>,
     *   total_refund_rupiah: int,
     *   payment_buckets: list<array{customer_payment_id: string, row_ids: list<string>, amount_rupiah: int}>
     * }
     */
    public function serialize(SelectedRowsRefundPlan $plan): array
    {
        return [
            'note_id' => $plan->noteId(),
            'selected_row_ids' => $plan->selectedRowIds(),
            'unpaid_row_ids' => $plan->unpaidRowIds(),
            'total_refund_rupiah' => $plan->totalRefundRupiah(),
            'payment_buckets' => array_map(
                static fn (SelectedRowsRefundPaymentBucket $bucket): array => $bucket->toArray(),
                $plan->paymentBuckets(),
            ),
        ];
    }
}
