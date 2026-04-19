<?php

declare(strict_types=1);

namespace App\Application\Reporting\UseCases;

use App\Application\Reporting\Services\OperationalProfitReportingReconciliationService;
use App\Application\Reporting\Services\OperationalProfitSummaryBuilder;
use App\Application\Shared\DTO\Result;
use App\Ports\Out\Reporting\OperationalProfitReportingSourceReaderPort;

final class GetOperationalProfitSummaryHandler
{
    public function __construct(
        private readonly OperationalProfitReportingSourceReaderPort $sourceReader,
        private readonly OperationalProfitSummaryBuilder $builder,
        private readonly OperationalProfitReportingReconciliationService $reconciliation,
    ) {
    }

    public function handle(string $fromDate, string $toDate): Result
    {
        $rawRow = $this->sourceReader->getOperationalProfitSummary($fromDate, $toDate);
        $row = $this->builder->build($rawRow);
        $expected = $this->sourceReader->getOperationalProfitReconciliation($fromDate, $toDate);

        $this->reconciliation->assertOperationalProfitSummaryMatches($row, $expected);

        return Result::success([
            'row' => $row->toArray(),
        ]);
    }
}
