<?php

declare(strict_types=1);

namespace App\Adapters\In\Http\Controllers\Admin\Reporting;

use App\Adapters\In\Http\Requests\Reporting\EmployeeDebtReportPageRequest;
use App\Adapters\In\Http\Support\ReportArrayPaginator;
use App\Application\Reporting\DTO\EmployeeDebtReportPageQuery;
use App\Application\Reporting\UseCases\GetEmployeeDebtReportDatasetHandler;
use Illuminate\Contracts\View\View;
use Illuminate\Routing\Controller;

final class EmployeeDebtReportPageController extends Controller
{
    public function __invoke(
        EmployeeDebtReportPageRequest $request,
        GetEmployeeDebtReportDatasetHandler $useCase,
        ReportArrayPaginator $paginator,
    ): View {
        $query = EmployeeDebtReportPageQuery::fromValidated($request->validated());
        $result = $useCase->handle($query->fromRecordedDate(), $query->toRecordedDate());
        $payload = is_array($result->data()) ? $result->data() : [];

        return view('admin.reporting.employee_debt.index', [
            'filters' => $query->toViewData(),
            'summary' => is_array($payload['summary'] ?? null) ? $payload['summary'] : [],
            'periodRows' => is_array($payload['period_rows'] ?? null) ? $payload['period_rows'] : [],
            'statusRows' => is_array($payload['status_rows'] ?? null) ? $payload['status_rows'] : [],
            'rows' => $paginator->paginate(
                is_array($payload['rows'] ?? null) ? $payload['rows'] : [],
                $request,
                'detail_page',
            ),
        ]);
    }
}
