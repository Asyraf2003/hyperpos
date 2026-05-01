<?php

declare(strict_types=1);

namespace App\Application\Reporting\Exports;

use PhpOffice\PhpSpreadsheet\Spreadsheet;

final class OperationalProfitReportExcelWorkbookBuilder
{
    public function __construct(
        private readonly OperationalProfitReportExcelSummarySheetWriter $summaryWriter,
    ) {}

    public function build(array $dataset, array $filters): Spreadsheet
    {
        $spreadsheet = new Spreadsheet;

        $this->summaryWriter->write(
            $spreadsheet->getActiveSheet(),
            is_array($dataset['row'] ?? null) ? $dataset['row'] : [],
            $filters,
        );

        $spreadsheet->setActiveSheetIndex(0);

        return $spreadsheet;
    }
}
