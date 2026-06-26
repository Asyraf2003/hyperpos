<?php

declare(strict_types=1);

namespace App\Application\Reporting\DTO;

final class TransactionSummaryPerNoteRow
{
    use TransactionSummaryPerNoteRowAccessors;

    public function __construct(
        private readonly string $noteId,
        private readonly string $transactionDate,
        private readonly string $customerName,
        private readonly int $grossTransactionRupiah,
        private readonly int $allocatedPaymentRupiah,
        private readonly int $refundedRupiah,
        private readonly int $refundDueRupiah,
        private readonly string $paymentStatusLabel,
        private readonly ?int $outstandingRupiah = null,
    ) {
    }

    /**
     * @return array{
     *   note_id:string,
     *   transaction_date:string,
     *   customer_name:string,
     *   gross_transaction_rupiah:int,
     *   allocated_payment_rupiah:int,
     *   refunded_rupiah:int,
     *   refund_due_rupiah:int,
     *   net_cash_collected_rupiah:int,
     *   outstanding_rupiah:int,
     *   payment_status_label:string
     * }
     */
    public function toArray(): array
    {
        return [
            'note_id' => $this->noteId(),
            'transaction_date' => $this->transactionDate(),
            'customer_name' => $this->customerName(),
            'gross_transaction_rupiah' => $this->grossTransactionRupiah(),
            'allocated_payment_rupiah' => $this->allocatedPaymentRupiah(),
            'refunded_rupiah' => $this->refundedRupiah(),
            'refund_due_rupiah' => $this->refundDueRupiah(),
            'net_cash_collected_rupiah' => $this->netCashCollectedRupiah(),
            'outstanding_rupiah' => $this->outstandingRupiah(),
            'payment_status_label' => $this->paymentStatusLabel(),
        ];
    }
}
