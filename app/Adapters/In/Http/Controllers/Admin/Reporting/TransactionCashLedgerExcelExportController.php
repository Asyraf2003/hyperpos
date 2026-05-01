<?php

declare(strict_types=1);

namespace App\Adapters\In\Http\Controllers\Admin\Reporting;

use App\Adapters\In\Http\Requests\Reporting\TransactionCashLedgerPageRequest;
use App\Application\Reporting\DTO\TransactionCashLedgerPageQuery;
use App\Application\Reporting\Exports\TransactionCashLedgerExcelWorkbookBuilder;
use App\Application\Reporting\Services\TransactionCashLedgerPeriodTableBuilder;
use App\Application\Reporting\Services\TransactionCashLedgerSummaryBuilder;
use App\Application\Reporting\UseCases\GetTransactionCashLedgerPerNoteHandler;
use Carbon\CarbonImmutable;
use Illuminate\Routing\Controller;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\Response;

final class TransactionCashLedgerExcelExportController extends Controller
{
    private const MAX_EXCEL_RANGE_DAYS = 366;

    public function __invoke(
        TransactionCashLedgerPageRequest $request,
        GetTransactionCashLedgerPerNoteHandler $useCase,
        TransactionCashLedgerSummaryBuilder $summaryBuilder,
        TransactionCashLedgerPeriodTableBuilder $periodBuilder,
        TransactionCashLedgerExcelWorkbookBuilder $workbookBuilder,
    ): Response {
        $query = TransactionCashLedgerPageQuery::fromValidated($request->validated());

        $rangeRejection = $this->rejectWhenExcelRangeIsTooLong(
            $query->fromEventDate(),
            $query->toEventDate(),
        );

        if ($rangeRejection instanceof Response) {
            return $rangeRejection;
        }

        $result = $useCase->handle($query->fromEventDate(), $query->toEventDate());
        $payload = is_array($result->data()) ? $result->data() : [];
        $rows = is_array($payload['rows'] ?? null) ? $payload['rows'] : [];

        $spreadsheet = $workbookBuilder->build([
            'summary' => $summaryBuilder->build($rows),
            'period_rows' => $periodBuilder->build($rows),
            'rows' => $rows,
        ], $query->toViewData());

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

    private function filename(TransactionCashLedgerPageQuery $query): string
    {
        return sprintf(
            'laporan-buku-kas-transaksi-%s-sampai-%s.xlsx',
            $query->fromEventDate(),
            $query->toEventDate(),
        );
    }
}
