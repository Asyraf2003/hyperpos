<?php

declare(strict_types=1);

namespace App\Application\Note\Services;

use App\Ports\Out\Payment\CustomerPaymentReaderPort;
use App\Ports\Out\Payment\CustomerRefundReaderPort;
use App\Ports\Out\Payment\PaymentAllocationReaderPort;
use App\Ports\Out\Payment\PaymentComponentAllocationReaderPort;

final class NoteRefundPaymentOptionsBuilder
{
    public function __construct(
        private readonly PaymentComponentAllocationReaderPort $paymentComponents,
        private readonly CustomerPaymentReaderPort $payments,
        private readonly PaymentAllocationReaderPort $allocations,
        private readonly CustomerRefundReaderPort $refunds,
    ) {
    }

    /**
     * @return list<array<string, int|string>>
     */
    public function build(string $noteId): array
    {
        $paymentIds = [];
        $seen = [];

        foreach ($this->paymentComponents->listByNoteId($noteId) as $allocation) {
            $paymentId = $allocation->customerPaymentId();

            if (isset($seen[$paymentId])) {
                continue;
            }

            $seen[$paymentId] = true;
            $paymentIds[] = $paymentId;
        }

        $options = [];

        foreach ($paymentIds as $paymentId) {
            $payment = $this->payments->getById($paymentId);

            if ($payment === null) {
                continue;
            }

            $allocated = $this->allocations->getTotalAllocatedAmountByCustomerPaymentIdAndNoteId($paymentId, $noteId);
            $allocated->ensureNotNegative('Total alokasi refund option tidak boleh negatif.');

            $refunded = $this->refunds->getTotalRefundedAmountByCustomerPaymentIdAndNoteId($paymentId, $noteId);
            $refunded->ensureNotNegative('Total refund refund option tidak boleh negatif.');

            $refundable = max($allocated->amount() - $refunded->amount(), 0);

            if ($refundable <= 0) {
                continue;
            }

            $options[] = [
                'value' => $paymentId,
                'payment_id' => $paymentId,
                'paid_at' => $payment->paidAt()->format('Y-m-d'),
                'allocated_rupiah' => $allocated->amount(),
                'refunded_rupiah' => $refunded->amount(),
                'refundable_rupiah' => $refundable,
                'label' => sprintf(
                    '%s - refundable %s',
                    $payment->paidAt()->format('Y-m-d'),
                    number_format($refundable, 0, ',', '.'),
                ),
            ];
        }

        return $options;
    }
}
