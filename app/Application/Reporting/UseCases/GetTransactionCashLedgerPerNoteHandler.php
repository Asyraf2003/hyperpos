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

        $this->reconciliation->assertTransactionCashLedgerMatches(
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
            'total_in_rupiah' => 0,
            'total_out_rupiah' => 0,
        ];

        foreach ($rawRows as $row) {
            $amount = (int) ($row['event_amount_rupiah'] ?? 0);
            $direction = (string) ($row['direction'] ?? '');

            if ($direction === 'in') {
                $expected['total_in_rupiah'] += $amount;
                continue;
            }

            if ($direction === 'out') {
                $expected['total_out_rupiah'] += $amount;
            }
        }

        return $expected;
    }
}
