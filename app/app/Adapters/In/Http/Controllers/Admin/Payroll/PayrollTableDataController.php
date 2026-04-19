<?php

declare(strict_types=1);

namespace App\Adapters\In\Http\Controllers\Admin\Payroll;

use App\Adapters\In\Http\Presenters\JsonPresenter;
use App\Adapters\In\Http\Requests\EmployeeFinance\PayrollTableQueryRequest;
use App\Application\EmployeeFinance\DTO\PayrollTableQuery;
use App\Application\EmployeeFinance\UseCases\GetPayrollTableHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;

final class PayrollTableDataController extends Controller
{
    public function __invoke(
        PayrollTableQueryRequest $request,
        GetPayrollTableHandler $useCase,
        JsonPresenter $presenter,
    ): JsonResponse {
        return $presenter->success($useCase->handle(PayrollTableQuery::fromValidated($request->validated())));
    }
}
