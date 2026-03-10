<?php

declare(strict_types=1);

namespace App\Adapters\In\Http\Controllers\IdentityAccess;

use App\Adapters\In\Http\Presenters\JsonPresenter;
use App\Adapters\In\Http\Requests\IdentityAccess\EnableAdminTransactionCapabilityRequest;
use App\Application\IdentityAccess\UseCases\EnableAdminTransactionCapabilityHandler;
use Illuminate\Http\JsonResponse;

final class EnableAdminTransactionCapabilityController
{
    public function __invoke(
        EnableAdminTransactionCapabilityRequest $request,
        EnableAdminTransactionCapabilityHandler $useCase,
        JsonPresenter $presenter,
    ): JsonResponse {
        $data = $request->validated();

        $result = $useCase->handle(
            (string) $data['target_actor_id'],
            (string) $data['performed_by_actor_id'],
        );

        if ($result->isFailure()) {
            return $presenter->failure($result);
        }

        return $presenter->success($result);
    }
}
