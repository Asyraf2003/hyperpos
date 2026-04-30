<?php

declare(strict_types=1);

namespace App\Adapters\In\Http\Controllers\Admin;

use App\Application\Reporting\UseCases\GetAdminDashboardAnalyticsHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;

final class AdminDashboardAnalyticsPayloadController extends Controller
{
    public function __invoke(GetAdminDashboardAnalyticsHandler $useCase): JsonResponse
    {
        return response()->json($useCase->handle());
    }
}
