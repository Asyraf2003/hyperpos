<?php

declare(strict_types=1);

namespace App\Adapters\In\Http\Controllers\Admin\Reporting;

use App\Adapters\In\Http\Requests\Reporting\OperationalExpenseReportPageRequest;
use App\Application\Reporting\DTO\OperationalExpenseReportPageQuery;
use App\Application\Reporting\UseCases\GetOperationalExpenseReportDatasetHandler;
use Illuminate\Contracts\View\View;
use Illuminate\Routing\Controller;

final class OperationalExpenseReportPageController extends Controller
{
    public function __invoke(
        OperationalExpenseReportPageRequest $request,
        GetOperationalExpenseReportDatasetHandler $useCase,
    ): View {
        $query = OperationalExpenseReportPageQuery::fromValidated($request->validated());
        $result = $useCase->handle($query->fromExpenseDate(), $query->toExpenseDate());
        $payload = is_array($result->data()) ? $result->data() : [];

        return view('admin.reporting.operational_expense.index', [
            'filters' => $query->toViewData(),
            'summary' => is_array($payload['summary'] ?? null) ? $payload['summary'] : [],
            'periodRows' => is_array($payload['period_rows'] ?? null) ? $payload['period_rows'] : [],
            'categoryRows' => is_array($payload['category_rows'] ?? null) ? $payload['category_rows'] : [],
            'rows' => is_array($payload['rows'] ?? null) ? $payload['rows'] : [],
        ]);
    }
}
