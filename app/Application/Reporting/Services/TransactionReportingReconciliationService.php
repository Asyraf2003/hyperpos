<?php

declare(strict_types=1);

namespace App\Application\Reporting\Services;

use App\Application\Reporting\DTO\TransactionSummaryPerNoteRow;

final class TransactionReportingReconciliationService
{
    /**
     * @param list<TransactionSummaryPerNoteRow> $rows
     * @param array{
     *   total_notes:int,
     *   gross_transaction_rupiah:int,
     *   allocated_payment_rupiah:int,
     *   refunded_rupiah:int
     * } $expected
     */
    public function assertTransactionSummaryMatches(array $rows, array $expected): void
    {
        $actualTotalNotes = count($rows);
        $actualGross = 0;
        $actualAllocated = 0;
        $actualRefunded = 0;

        foreach ($rows as $row) {
            $actualGross += $row->grossTransactionRupiah();
            $actualAllocated += $row->allocatedPaymentRupiah();
            $actualRefunded += $row->refundedRupiah();
        }

        if ($actualTotalNotes !== $expected['total_notes']) {
            throw new \RuntimeException('Reporting mismatch: total_notes.');
        }

        if ($actualGross !== $expected['gross_transaction_rupiah']) {
            throw new \RuntimeException('Reporting mismatch: gross_transaction_rupiah.');
        }

        if ($actualAllocated !== $expected['allocated_payment_rupiah']) {
            throw new \RuntimeException('Reporting mismatch: allocated_payment_rupiah.');
        }

        if ($actualRefunded !== $expected['refunded_rupiah']) {
            throw new \RuntimeException('Reporting mismatch: refunded_rupiah.');
        }
    }
}
