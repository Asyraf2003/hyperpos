<?php

declare(strict_types=1);

namespace App\Adapters\In\Http\Controllers\Admin\Expense;

use App\Adapters\In\Http\Presenters\JsonPresenter;
use App\Adapters\In\Http\Requests\Expense\ExpenseCategoryTableQueryRequest;
use App\Application\Expense\DTO\ExpenseCategoryTableQuery;
use App\Application\Expense\UseCases\GetExpenseCategoryTableHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;

final class ExpenseCategoryTableDataController extends Controller
{
    public function __invoke(
        ExpenseCategoryTableQueryRequest $request,
        GetExpenseCategoryTableHandler $useCase,
        JsonPresenter $presenter,
    ): JsonResponse {
        $result = $useCase->handle(ExpenseCategoryTableQuery::fromValidated($request->validated()));

        return $presenter->success($result);
    }
}
