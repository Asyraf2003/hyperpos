<?php

declare(strict_types=1);

namespace App\Application\Reporting\UseCases;

use App\Application\Reporting\Services\OperationalExpenseCategoryBreakdownBuilder;
use App\Application\Reporting\Services\OperationalExpensePeriodBreakdownBuilder;
use App\Application\Reporting\Services\OperationalExpenseReportSummaryBuilder;
use App\Application\Shared\DTO\Result;

final class GetOperationalExpenseReportDatasetHandler
{
    public function __construct(
        private readonly GetOperationalExpenseSummaryHandler $summaryHandler,
        private readonly OperationalExpenseReportSummaryBuilder $summary,
        private readonly OperationalExpensePeriodBreakdownBuilder $periods,
        private readonly OperationalExpenseCategoryBreakdownBuilder $categories,
    ) {
    }

    public function handle(string $fromExpenseDate, string $toExpenseDate): Result
    {
        $result = $this->summaryHandler->handle($fromExpenseDate, $toExpenseDate);

        if ($result->isFailure()) {
            return $result;
        }

        $data = $result->data();
        $rows = is_array($data) && is_array($data['rows'] ?? null)
            ? $data['rows']
            : [];

        $categoryRows = $this->categories->build($rows);

        return Result::success([
            'rows' => $rows,
            'summary' => $this->summary->build($rows, $categoryRows),
            'period_rows' => $this->periods->build($rows),
            'category_rows' => $categoryRows,
        ]);
    }
}
