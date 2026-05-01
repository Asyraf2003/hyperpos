<?php

declare(strict_types=1);

namespace App\Adapters\In\Http\Controllers\Admin\Reporting;

use App\Adapters\In\Http\Requests\Reporting\PayrollReportPageRequest;
use App\Application\Reporting\DTO\PayrollReportPageQuery;
use App\Application\Reporting\Exports\PayrollReportExcelWorkbookBuilder;
use App\Application\Reporting\UseCases\GetPayrollReportDatasetHandler;
use Carbon\CarbonImmutable;
use Illuminate\Routing\Controller;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\Response;

final class PayrollReportExcelExportController extends Controller
{
    private const MAX_EXCEL_RANGE_DAYS = 366;

    public function __invoke(
        PayrollReportPageRequest $request,
        GetPayrollReportDatasetHandler $useCase,
        PayrollReportExcelWorkbookBuilder $workbookBuilder,
    ): Response {
        $query = PayrollReportPageQuery::fromValidated($request->validated());

        $rangeRejection = $this->rejectWhenExcelRangeIsTooLong(
            $query->fromDisbursementDate(),
            $query->toDisbursementDate(),
        );

        if ($rangeRejection instanceof Response) {
            return $rangeRejection;
        }

        $result = $useCase->handle($query->fromDisbursementDate(), $query->toDisbursementDate());
        $dataset = is_array($result->data()) ? $result->data() : [];
        $spreadsheet = $workbookBuilder->build($dataset, $query->toViewData());

        return response()->streamDownload(
            static function () use ($spreadsheet): void {
                $writer = new Xlsx($spreadsheet);
                $writer->save('php://output');
                $spreadsheet->disconnectWorksheets();
            },
            $this->filename($query),
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

    private function filename(PayrollReportPageQuery $query): string
    {
        return sprintf(
            'laporan-gaji-%s-sampai-%s.xlsx',
            $query->fromDisbursementDate(),
            $query->toDisbursementDate(),
        );
    }
}
