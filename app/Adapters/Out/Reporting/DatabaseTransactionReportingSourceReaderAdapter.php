<?php

declare(strict_types=1);

namespace App\Adapters\Out\Reporting;

use App\Ports\Out\Reporting\TransactionReportingSourceReaderPort;

final class DatabaseTransactionReportingSourceReaderAdapter implements TransactionReportingSourceReaderPort
{
    public function getTransactionSummaryPerNoteRows(
        string $fromTransactionDate,
        string $toTransactionDate,
    ): array {
        return [];
    }

    public function getTransactionSummaryPerNoteReconciliation(
        string $fromTransactionDate,
        string $toTransactionDate,
    ): array {
        return [
            'total_notes' => 0,
            'gross_transaction_rupiah' => 0,
            'allocated_payment_rupiah' => 0,
            'refunded_rupiah' => 0,
        ];
    }

    public function getTransactionCashLedgerPerNoteRows(
        string $fromEventDate,
        string $toEventDate,
    ): array {
        return [];
    }

    public function getTransactionCashLedgerPerNoteReconciliation(
        string $fromEventDate,
        string $toEventDate,
    ): array {
        return [
            'total_in_rupiah' => 0,
            'total_out_rupiah' => 0,
        ];
    }
}
