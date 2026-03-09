<?php

declare(strict_types=1);

namespace App\Adapters\In\Http\Controllers;

use App\Adapters\In\Http\Presenters\JsonPresenter;
use App\Ports\In\HealthCheckUseCase;
use Illuminate\Http\JsonResponse;

final class HealthCheckController
{
    public function __invoke(
        HealthCheckUseCase $useCase,
        JsonPresenter $presenter,
    ): JsonResponse {
        return $presenter->success($useCase->execute());
    }
}
