<?php

declare(strict_types=1);

namespace App\Adapters\In\Http\Controllers\Admin\Employee;

use App\Adapters\In\Http\Presenters\JsonPresenter;
use App\Adapters\In\Http\Requests\EmployeeFinance\EmployeePayrollTableQueryRequest;
use App\Application\EmployeeFinance\DTO\EmployeePayrollTableQuery;
use App\Application\EmployeeFinance\UseCases\GetEmployeePayrollTableHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;

final class EmployeePayrollTableDataController extends Controller
{
    public function __invoke(
        string $employeeId,
        EmployeePayrollTableQueryRequest $request,
        GetEmployeePayrollTableHandler $useCase,
        JsonPresenter $presenter,
    ): JsonResponse {
        return $presenter->success(
            $useCase->handle(
                $employeeId,
                EmployeePayrollTableQuery::fromValidated($request->validated())
            )
        );
    }
}
