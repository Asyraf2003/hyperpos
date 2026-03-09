<?php

declare(strict_types=1);

namespace App\Adapters\In\Http\Presenters;

use App\Adapters\In\Http\Presenters\Response\JsonResultResponder;
use App\Application\Shared\DTO\Result;
use Illuminate\Http\JsonResponse;

final class JsonPresenter
{
    public function __construct(
        private readonly JsonResultResponder $responder,
    ) {
    }

    public function success(Result $result, int $status = 200): JsonResponse
    {
        return $this->responder->success($result, $status);
    }

    public function failure(Result $result, int $status = 422): JsonResponse
    {
        return $this->responder->failure($result, $status);
    }
}
