<?php

declare(strict_types=1);

namespace App\Adapters\In\Http\Controllers\Admin\Reporting;

use App\Adapters\In\Http\Requests\Reporting\OperationalExpenseReportPageRequest;
use App\Application\Reporting\DTO\OperationalExpenseReportPageQuery;
use App\Application\Reporting\Exports\OperationalExpenseReportExcelWorkbookBuilder;
use App\Application\Reporting\UseCases\GetOperationalExpenseReportDatasetHandler;
use Carbon\CarbonImmutable;
use Illuminate\Routing\Controller;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\Response;

final class OperationalExpenseReportExcelExportController extends Controller
{
    private const MAX_EXCEL_RANGE_DAYS = 366;

    public function __invoke(
        OperationalExpenseReportPageRequest $request,
        GetOperationalExpenseReportDatasetHandler $useCase,
        OperationalExpenseReportExcelWorkbookBuilder $workbookBuilder,
    ): Response {
        $query = OperationalExpenseReportPageQuery::fromValidated($request->validated());

        $rangeRejection = $this->rejectWhenExcelRangeIsTooLong(
            $query->fromExpenseDate(),
            $query->toExpenseDate(),
        );

        if ($rangeRejection instanceof Response) {
            return $rangeRejection;
        }

        $result = $useCase->handle($query->fromExpenseDate(), $query->toExpenseDate());
        $dataset = is_array($result->data()) ? $result->data() : [];
        $spreadsheet = $workbookBuilder->build($dataset, $query->toViewData());

        $filename = sprintf(
            'laporan-biaya-operasional-%s-sampai-%s.xlsx',
            $query->fromExpenseDate(),
            $query->toExpenseDate(),
        );

        return response()->streamDownload(
            static function () use ($spreadsheet): void {
                $writer = new Xlsx($spreadsheet);
                $writer->save('php://output');
                $spreadsheet->disconnectWorksheets();
            },
            $filename,
            ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'],
        );
    }

    private function rejectWhenExcelRangeIsTooLong(string $from, string $to): ?Response
    {
        $fromDate = CarbonImmutable::parse($from);
        $toDate = CarbonImmutable::parse($to);

        if (($fromDate->diffInDays($toDate) + 1) <= self::MAX_EXCEL_RANGE_DAYS) {
            return null;
        }

        return response('Export Excel maksimal 366 hari.', 422);
    }
}
