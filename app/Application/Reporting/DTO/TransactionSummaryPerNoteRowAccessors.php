<?php

declare(strict_types=1);

namespace App\Application\Reporting\DTO;

trait TransactionSummaryPerNoteRowAccessors
{
    public function noteId(): string
    {
        return $this->noteId;
    }

    public function transactionDate(): string
    {
        return $this->transactionDate;
    }

    public function customerName(): string
    {
        return $this->customerName;
    }

    public function grossTransactionRupiah(): int
    {
        return $this->grossTransactionRupiah;
    }

    public function allocatedPaymentRupiah(): int
    {
        return $this->allocatedPaymentRupiah;
    }

    public function refundedRupiah(): int
    {
        return $this->refundedRupiah;
    }

    public function refundDueRupiah(): int
    {
        return $this->refundDueRupiah;
    }

    public function netCashCollectedRupiah(): int
    {
        return $this->allocatedPaymentRupiah - $this->refundedRupiah;
    }

    public function outstandingRupiah(): int
    {
        if ($this->outstandingRupiah !== null) {
            return max($this->outstandingRupiah, 0);
        }

        return max($this->grossTransactionRupiah - $this->allocatedPaymentRupiah + $this->refundedRupiah, 0);
    }

    public function paymentStatusLabel(): string
    {
        return $this->paymentStatusLabel;
    }
}
