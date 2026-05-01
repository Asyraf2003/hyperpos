<?php

declare(strict_types=1);

namespace App\Adapters\In\Http\Controllers\Admin\Reporting;

use App\Adapters\In\Http\Requests\Reporting\OperationalExpenseReportPageRequest;
use App\Application\Reporting\DTO\OperationalExpenseReportPageQuery;
use App\Application\Reporting\Exports\OperationalExpenseReportPdfViewDataBuilder;
use App\Application\Reporting\UseCases\GetOperationalExpenseReportDatasetHandler;
use Carbon\CarbonImmutable;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;

final class OperationalExpenseReportPdfExportController extends Controller
{
    private const MAX_PDF_RANGE_DAYS = 30;

    public function __invoke(
        OperationalExpenseReportPageRequest $request,
        GetOperationalExpenseReportDatasetHandler $useCase,
        OperationalExpenseReportPdfViewDataBuilder $viewDataBuilder,
        ViewFactory $viewFactory,
    ): Response {
        $query = OperationalExpenseReportPageQuery::fromValidated($request->validated());

        $rangeRejection = $this->rejectWhenPdfRangeIsTooLong(
            $query->fromExpenseDate(),
            $query->toExpenseDate(),
        );

        if ($rangeRejection instanceof Response) {
            return $rangeRejection;
        }

        $result = $useCase->handle($query->fromExpenseDate(), $query->toExpenseDate());
        $dataset = is_array($result->data()) ? $result->data() : [];

        $html = $viewFactory->make(
            'admin.reporting.operational_expense.export_pdf',
            $viewDataBuilder->build($dataset, $query->toViewData()),
        )->render();

        $dompdf = new Dompdf($this->options());
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('A4');
        $dompdf->render();

        return response($dompdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $this->filename($query) . '"',
        ]);
    }

    private function rejectWhenPdfRangeIsTooLong(string $from, string $to): ?Response
    {
        $fromDate = CarbonImmutable::parse($from);
        $toDate = CarbonImmutable::parse($to);

        if ($fromDate->diffInDays($toDate) <= self::MAX_PDF_RANGE_DAYS) {
            return null;
        }

        return response('Export PDF maksimal 1 bulan.', 422);
    }

    private function filename(OperationalExpenseReportPageQuery $query): string
    {
        return sprintf(
            'laporan-biaya-operasional-%s-sampai-%s.pdf',
            $query->fromExpenseDate(),
            $query->toExpenseDate(),
        );
    }

    private function options(): Options
    {
        $options = new Options();
        $options->set('defaultFont', 'DejaVu Sans');
        $options->set('isRemoteEnabled', false);

        return $options;
    }
}
