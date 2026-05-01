<?php

declare(strict_types=1);

namespace App\Adapters\In\Http\Controllers\Admin\Reporting;

use App\Adapters\In\Http\Requests\Reporting\OperationalProfitReportPageRequest;
use App\Application\Reporting\DTO\OperationalProfitReportPageQuery;
use App\Application\Reporting\Exports\OperationalProfitReportExcelWorkbookBuilder;
use App\Application\Reporting\UseCases\GetOperationalProfitSummaryHandler;
use Illuminate\Routing\Controller;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\Response;

final class OperationalProfitReportExcelExportController extends Controller
{
    public function __invoke(
        OperationalProfitReportPageRequest $request,
        GetOperationalProfitSummaryHandler $useCase,
        OperationalProfitReportExcelWorkbookBuilder $workbookBuilder,
    ): Response {
        $query = OperationalProfitReportPageQuery::fromValidated($request->validated());

        $result = $useCase->handle($query->fromDate(), $query->toDate());
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

    private function filename(OperationalProfitReportPageQuery $query): string
    {
        return sprintf(
            'laporan-laba-kas-operasional-%s-sampai-%s.xlsx',
            $query->fromDate(),
            $query->toDate(),
        );
    }
}
