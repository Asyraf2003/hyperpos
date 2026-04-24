<?php

declare(strict_types=1);

namespace App\Application\Note\Services;

use App\Application\Payment\DTO\SelectedRowsRefundPaymentBucket;
use App\Core\Payment\PaymentComponentAllocation\PaymentComponentAllocation;

final class SelectedRowsRefundBucketsBuilder
{
    /**
     * @param list<string> $selectedRowIds
     * @param list<PaymentComponentAllocation> $allocations
     * @return list<SelectedRowsRefundPaymentBucket>
     */
    public function build(array $selectedRowIds, array $allocations): array
    {
        $groups = [];

        foreach ($allocations as $allocation) {
            if (!in_array($allocation->workItemId(), $selectedRowIds, true)) {
                continue;
            }

            $paymentId = $allocation->customerPaymentId();

            $groups[$paymentId] ??= [
                'row_ids' => [],
                'amount_rupiah' => 0,
            ];

            $groups[$paymentId]['row_ids'][] = $allocation->workItemId();
            $groups[$paymentId]['amount_rupiah'] += $allocation->allocatedAmountRupiah()->amount();
        }

        return array_values(array_map(
            static fn (string $paymentId, array $group): SelectedRowsRefundPaymentBucket =>
                SelectedRowsRefundPaymentBucket::create(
                    $paymentId,
                    array_values(array_unique($group['row_ids'])),
                    (int) $group['amount_rupiah'],
                ),
            array_keys($groups),
            array_values($groups),
        ));
    }
}
