<?php

declare(strict_types=1);

namespace App\Adapters\In\Http\Controllers\Admin\Employee;

use App\Adapters\In\Http\Presenters\JsonPresenter;
use App\Adapters\In\Http\Requests\EmployeeFinance\EmployeeTableQueryRequest;
use App\Application\EmployeeFinance\DTO\EmployeeTableQuery;
use App\Application\EmployeeFinance\UseCases\GetEmployeeTableHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;

final class EmployeeTableDataController extends Controller
{
    public function __invoke(
        EmployeeTableQueryRequest $request,
        GetEmployeeTableHandler $useCase,
        JsonPresenter $presenter,
    ): JsonResponse {
        return $presenter->success($useCase->handle(EmployeeTableQuery::fromValidated($request->validated())));
    }
}
