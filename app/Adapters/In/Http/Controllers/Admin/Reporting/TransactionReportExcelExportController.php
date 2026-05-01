<?php

declare(strict_types=1);

namespace App\Adapters\In\Http\Controllers\Admin\Reporting;

use App\Adapters\In\Http\Requests\Reporting\TransactionReportPageRequest;
use App\Application\Reporting\DTO\TransactionReportPageQuery;
use App\Application\Reporting\Exports\TransactionReportExcelWorkbookBuilder;
use App\Application\Reporting\UseCases\GetTransactionReportDatasetHandler;
use Illuminate\Routing\Controller;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class TransactionReportExcelExportController extends Controller
{
    public function __invoke(
        TransactionReportPageRequest $request,
        GetTransactionReportDatasetHandler $useCase,
        TransactionReportExcelWorkbookBuilder $workbookBuilder,
    ): StreamedResponse {
        $query = TransactionReportPageQuery::fromValidated($request->validated());
        $result = $useCase->handle($query->fromTransactionDate(), $query->toTransactionDate());
        $dataset = is_array($result->data()) ? $result->data() : [];
        $spreadsheet = $workbookBuilder->build($dataset, $query->toViewData());

        $filename = sprintf(
            'laporan-transaksi-%s-sampai-%s.xlsx',
            $query->fromTransactionDate(),
            $query->toTransactionDate(),
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
}
