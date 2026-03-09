<?php

declare(strict_types=1);

namespace App\Adapters\In\Http\Presenters\Response;

use App\Application\Shared\DTO\Result;
use Illuminate\Http\JsonResponse;

final class JsonResultResponder
{
    public function success(Result $result, int $status = 200): JsonResponse
    {
        return response()->json($result->toArray(), $status);
    }

    public function failure(Result $result, int $status = 422): JsonResponse
    {
        return response()->json($result->toArray(), $status);
    }
}
