<?php

declare(strict_types=1);

namespace App\Application\Reporting\UseCases;

use App\Application\Reporting\Services\TransactionCashLedgerPerNoteBuilder;
use App\Application\Reporting\Services\TransactionReportingReconciliationService;
use App\Application\Shared\DTO\Result;
use App\Ports\Out\Reporting\TransactionReportingSourceReaderPort;

final class GetTransactionCashLedgerPerNoteHandler
{
    public function __construct(
        private readonly TransactionReportingSourceReaderPort $sourceReader,
        private readonly TransactionCashLedgerPerNoteBuilder $builder,
        private readonly TransactionReportingReconciliationService $reconciliation,
    ) {
    }

    public function handle(string $fromEventDate, string $toEventDate): Result
    {
        $rawRows = $this->sourceReader->getTransactionCashLedgerPerNoteRows(
            $fromEventDate,
            $toEventDate,
        );

        $rows = $this->builder->build($rawRows);

        $expected = $this->sourceReader->getTransactionCashLedgerPerNoteReconciliation(
            $fromEventDate,
            $toEventDate,
        );

        $this->reconciliation->assertTransactionCashLedgerMatches($rows, $expected);

        return Result::success([
            'rows' => array_map(
                static fn ($row): array => $row->toArray(),
                $rows,
            ),
        ]);
    }
}
