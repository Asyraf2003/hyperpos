<?php

declare(strict_types=1);

namespace App\Adapters\In\Http\Controllers\Admin\Reporting;

use App\Adapters\In\Http\Requests\Reporting\InventoryStockValueReportPageRequest;
use App\Application\Reporting\DTO\InventoryStockValueReportPageQuery;
use App\Application\Reporting\Exports\InventoryStockValueReportExcelWorkbookBuilder;
use App\Application\Reporting\UseCases\GetInventoryStockValueReportDatasetHandler;
use Carbon\CarbonImmutable;
use Illuminate\Routing\Controller;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\Response;

final class InventoryStockValueReportExcelExportController extends Controller
{
    private const MAX_EXCEL_RANGE_DAYS = 366;

    public function __invoke(
        InventoryStockValueReportPageRequest $request,
        GetInventoryStockValueReportDatasetHandler $useCase,
        InventoryStockValueReportExcelWorkbookBuilder $workbookBuilder,
    ): Response {
        $query = InventoryStockValueReportPageQuery::fromValidated($request->validated());

        $rangeRejection = $this->rejectWhenExcelRangeIsTooLong(
            $query->fromMutationDate(),
            $query->toMutationDate(),
        );

        if ($rangeRejection instanceof Response) {
            return $rangeRejection;
        }

        $result = $useCase->handle($query->fromMutationDate(), $query->toMutationDate());
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

    private function filename(InventoryStockValueReportPageQuery $query): string
    {
        return sprintf(
            'laporan-stok-persediaan-%s-sampai-%s.xlsx',
            $query->fromMutationDate(),
            $query->toMutationDate(),
        );
    }
}
