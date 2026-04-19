<?php

declare(strict_types=1);

namespace App\Application\Note\Services;

final class CreateTransactionWorkspaceSuccessMessageBuilder
{
    /**
     * @param array{decision:string,amount_paid_rupiah:int,change_rupiah:int} $paymentSummary
     */
    public function build(array $paymentSummary): string
    {
        if ($paymentSummary['decision'] === 'skip') {
            return 'Nota workspace berhasil dibuat.';
        }

        if ($paymentSummary['change_rupiah'] > 0) {
            return 'Nota dan pembayaran berhasil dicatat. Kembalian: ' . number_format($paymentSummary['change_rupiah'], 0, ',', '.');
        }

        return 'Nota dan pembayaran berhasil dicatat.';
    }
}
