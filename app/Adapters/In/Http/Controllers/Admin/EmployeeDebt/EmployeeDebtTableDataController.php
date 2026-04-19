<?php

declare(strict_types=1);

namespace App\Adapters\In\Http\Controllers\Admin\EmployeeDebt;

use App\Adapters\In\Http\Presenters\JsonPresenter;
use App\Adapters\In\Http\Requests\EmployeeFinance\EmployeeDebtTableQueryRequest;
use App\Application\EmployeeFinance\DTO\EmployeeDebtTableQuery;
use App\Application\EmployeeFinance\UseCases\GetEmployeeDebtTableHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;

final class EmployeeDebtTableDataController extends Controller
{
    public function __invoke(
        EmployeeDebtTableQueryRequest $request,
        GetEmployeeDebtTableHandler $useCase,
        JsonPresenter $presenter,
    ): JsonResponse {
        return $presenter->success($useCase->handle(EmployeeDebtTableQuery::fromValidated($request->validated())));
    }
}
