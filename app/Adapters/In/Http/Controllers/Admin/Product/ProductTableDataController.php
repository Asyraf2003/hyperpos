<?php

declare(strict_types=1);

namespace App\Adapters\In\Http\Controllers\Admin\Product;

use App\Adapters\In\Http\Presenters\JsonPresenter;
use App\Adapters\In\Http\Requests\ProductCatalog\ProductTableQueryRequest;
use App\Application\ProductCatalog\DTO\ProductTableQuery;
use App\Application\ProductCatalog\UseCases\GetProductTableHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;

final class ProductTableDataController extends Controller
{
    public function __invoke(
        ProductTableQueryRequest $request,
        GetProductTableHandler $useCase,
        JsonPresenter $presenter,
    ): JsonResponse {
        $result = $useCase->handle(ProductTableQuery::fromValidated($request->validated()));

        return $presenter->success($result);
    }
}
