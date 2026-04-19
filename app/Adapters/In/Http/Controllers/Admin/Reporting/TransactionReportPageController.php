<?php

declare(strict_types=1);

namespace App\Adapters\In\Http\Controllers\Admin\Reporting;

use App\Adapters\In\Http\Requests\Reporting\TransactionReportPageRequest;
use App\Application\Reporting\DTO\TransactionReportPageQuery;
use App\Application\Reporting\UseCases\GetTransactionReportDatasetHandler;
use Illuminate\Contracts\View\View;
use Illuminate\Routing\Controller;

final class TransactionReportPageController extends Controller
{
    public function __invoke(
        TransactionReportPageRequest $request,
        GetTransactionReportDatasetHandler $useCase,
    ): View {
        $query = TransactionReportPageQuery::fromValidated($request->validated());
        $result = $useCase->handle($query->fromTransactionDate(), $query->toTransactionDate());
        $payload = is_array($result->data()) ? $result->data() : [];

        return view('admin.reporting.transaction_summary.index', [
            'filters' => $query->toViewData(),
            'summary' => is_array($payload['summary'] ?? null) ? $payload['summary'] : [],
            'periodRows' => is_array($payload['period_rows'] ?? null) ? $payload['period_rows'] : [],
            'customerRows' => is_array($payload['customer_rows'] ?? null) ? $payload['customer_rows'] : [],
            'rows' => is_array($payload['rows'] ?? null) ? $payload['rows'] : [],
        ]);
    }
}
