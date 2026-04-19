<?php

declare(strict_types=1);

namespace App\Application\Reporting\UseCases;

use App\Application\Reporting\Services\PayrollReportModeBreakdownBuilder;
use App\Application\Reporting\Services\PayrollReportPeriodBreakdownBuilder;
use App\Application\Reporting\Services\PayrollReportRowBuilder;
use App\Application\Reporting\Services\PayrollReportSummaryBuilder;
use App\Application\Reporting\Services\PayrollReportingReconciliationService;
use App\Application\Shared\DTO\Result;
use App\Ports\Out\Reporting\PayrollReportingSourceReaderPort;

final class GetPayrollReportDatasetHandler
{
    public function __construct(
        private readonly PayrollReportingSourceReaderPort $sourceReader,
        private readonly PayrollReportRowBuilder $rows,
        private readonly PayrollReportingReconciliationService $reconciliation,
        private readonly PayrollReportPeriodBreakdownBuilder $periods,
        private readonly PayrollReportModeBreakdownBuilder $modes,
        private readonly PayrollReportSummaryBuilder $summary,
    ) {
    }

    public function handle(string $fromDate, string $toDate): Result
    {
        $builtRows = $this->rows->build($this->sourceReader->getPayrollReportRows($fromDate, $toDate));
        $this->reconciliation->assertPayrollReportMatches(
            $builtRows,
            $this->sourceReader->getPayrollReportReconciliation($fromDate, $toDate),
        );

        $rows = array_map(static fn (object $row): array => $row->toArray(), $builtRows);
        $modeRows = $this->modes->build($rows);

        return Result::success([
            'rows' => $rows,
            'summary' => $this->summary->build($rows, $modeRows, $fromDate, $toDate),
            'period_rows' => $this->periods->build($rows),
            'mode_rows' => $modeRows,
        ]);
    }
}
