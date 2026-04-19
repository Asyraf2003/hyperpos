<?php

declare(strict_types=1);

namespace App\Application\Reporting\Services;

use App\Application\Reporting\DTO\TransactionCashLedgerPerNoteRow;
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

    /**
     * @param list<TransactionCashLedgerPerNoteRow> $rows
     * @param array{
     *   total_in_rupiah:int,
     *   total_out_rupiah:int
     * } $expected
     */
    public function assertTransactionCashLedgerMatches(array $rows, array $expected): void
    {
        $actualIn = 0;
        $actualOut = 0;

        foreach ($rows as $row) {
            if ($row->direction() === 'in') {
                $actualIn += $row->eventAmountRupiah();
                continue;
            }

            if ($row->direction() === 'out') {
                $actualOut += $row->eventAmountRupiah();
                continue;
            }

            throw new \RuntimeException('Reporting mismatch: invalid ledger direction.');
        }

        if ($actualIn !== $expected['total_in_rupiah']) {
            throw new \RuntimeException('Reporting mismatch: total_in_rupiah.');
        }

        if ($actualOut !== $expected['total_out_rupiah']) {
            throw new \RuntimeException('Reporting mismatch: total_out_rupiah.');
        }
    }
}
