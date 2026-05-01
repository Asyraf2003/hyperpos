<?php

declare(strict_types=1);

namespace App\Application\Reporting\Services;

final class TransactionPaymentStatusLabelResolver
{
    public function resolve(
        int $grossTransactionRupiah,
        int $allocatedPaymentRupiah,
        int $refundedRupiah,
    ): string {
        $netCashCollectedRupiah = $allocatedPaymentRupiah - $refundedRupiah;
        $outstandingRupiah = $grossTransactionRupiah - $allocatedPaymentRupiah + $refundedRupiah;

        if ($refundedRupiah > 0 && $netCashCollectedRupiah <= 0) {
            return 'Refund Penuh';
        }

        if ($refundedRupiah > 0) {
            return 'Ada Refund';
        }

        if ($allocatedPaymentRupiah <= 0 && $outstandingRupiah > 0) {
            return 'Belum Dibayar';
        }

        if ($allocatedPaymentRupiah > 0 && $outstandingRupiah > 0) {
            return 'Sebagian';
        }

        return 'Lunas';
    }
}
