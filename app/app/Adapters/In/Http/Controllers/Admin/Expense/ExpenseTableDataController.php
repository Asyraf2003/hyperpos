<?php

declare(strict_types=1);

namespace App\Adapters\In\Http\Controllers\Admin\Expense;

use App\Adapters\In\Http\Presenters\JsonPresenter;
use App\Adapters\In\Http\Requests\Expense\ExpenseTableQueryRequest;
use App\Application\Expense\DTO\ExpenseTableQuery;
use App\Application\Expense\UseCases\GetExpenseTableHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;

final class ExpenseTableDataController extends Controller
{
    public function __invoke(
        ExpenseTableQueryRequest $request,
        GetExpenseTableHandler $useCase,
        JsonPresenter $presenter,
    ): JsonResponse {
        $result = $useCase->handle(ExpenseTableQuery::fromValidated($request->validated()));

        return $presenter->success($result);
    }
}
