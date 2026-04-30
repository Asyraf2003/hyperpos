<?php

declare(strict_types=1);

namespace App\Application\Reporting\UseCases;

use App\Application\Reporting\Services\TransactionReportingReconciliationService;
use App\Application\Reporting\Services\TransactionSummaryPerNoteBuilder;
use App\Application\Shared\DTO\Result;
use App\Ports\Out\Reporting\TransactionReportingSourceReaderPort;

final class GetTransactionSummaryPerNoteHandler
{
    public function __construct(
        private readonly TransactionReportingSourceReaderPort $sourceReader,
        private readonly TransactionSummaryPerNoteBuilder $builder,
        private readonly TransactionReportingReconciliationService $reconciliation,
    ) {
    }

    public function handle(string $fromTransactionDate, string $toTransactionDate): Result
    {
        $rawRows = $this->sourceReader->getTransactionSummaryPerNoteRows(
            $fromTransactionDate,
            $toTransactionDate,
        );

        $rows = $this->builder->build($rawRows);

        $this->reconciliation->assertTransactionSummaryMatches(
            $rows,
            $this->expectedFromRawRows($rawRows),
        );

        return Result::success([
            'rows' => array_map(
                static fn ($row): array => $row->toArray(),
                $rows,
            ),
        ]);
    }

    private function expectedFromRawRows(array $rawRows): array
    {
        $expected = [
            'total_notes' => count($rawRows),
            'gross_transaction_rupiah' => 0,
            'allocated_payment_rupiah' => 0,
            'refunded_rupiah' => 0,
        ];

        foreach ($rawRows as $row) {
            $expected['gross_transaction_rupiah'] += (int) ($row['gross_transaction_rupiah'] ?? 0);
            $expected['allocated_payment_rupiah'] += (int) ($row['allocated_payment_rupiah'] ?? 0);
            $expected['refunded_rupiah'] += (int) ($row['refunded_rupiah'] ?? 0);
        }

        return $expected;
    }
}
