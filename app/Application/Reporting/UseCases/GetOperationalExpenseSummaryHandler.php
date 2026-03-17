<?php

declare(strict_types=1);

namespace App\Application\Reporting\UseCases;

use App\Application\Reporting\Services\OperationalExpenseReportingReconciliationService;
use App\Application\Reporting\Services\OperationalExpenseSummaryBuilder;
use App\Application\Shared\DTO\Result;
use App\Ports\Out\Reporting\OperationalExpenseReportingSourceReaderPort;

final class GetOperationalExpenseSummaryHandler
{
    public function __construct(
        private readonly OperationalExpenseReportingSourceReaderPort $sourceReader,
        private readonly OperationalExpenseSummaryBuilder $builder,
        private readonly OperationalExpenseReportingReconciliationService $reconciliation,
    ) {
    }

    public function handle(string $fromExpenseDate, string $toExpenseDate): Result
    {
        $rawRows = $this->sourceReader->getOperationalExpenseSummaryRows(
            $fromExpenseDate,
            $toExpenseDate,
        );

        $rows = $this->builder->build($rawRows);

        $expected = $this->sourceReader->getOperationalExpenseSummaryReconciliation(
            $fromExpenseDate,
            $toExpenseDate,
        );

        $this->reconciliation->assertOperationalExpenseSummaryMatches($rows, $expected);

        return Result::success([
            'rows' => array_map(
                static fn ($row): array => $row->toArray(),
                $rows,
            ),
        ]);
    }
}
