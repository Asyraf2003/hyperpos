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

        $expected = $this->sourceReader->getTransactionSummaryPerNoteReconciliation(
            $fromTransactionDate,
            $toTransactionDate,
        );

        $this->reconciliation->assertTransactionSummaryMatches($rows, $expected);

        return Result::success([
            'rows' => array_map(
                static fn ($row): array => $row->toArray(),
                $rows,
            ),
        ]);
    }
}
