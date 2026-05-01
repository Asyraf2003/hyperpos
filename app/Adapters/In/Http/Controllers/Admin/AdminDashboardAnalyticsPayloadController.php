<?php

declare(strict_types=1);

namespace App\Adapters\In\Http\Controllers\Admin;

use App\Application\Reporting\UseCases\GetAdminDashboardAnalyticsHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

final class AdminDashboardAnalyticsPayloadController extends Controller
{
    public function __invoke(Request $request, GetAdminDashboardAnalyticsHandler $useCase): JsonResponse
    {
        $month = $request->query('month');

        return response()->json($useCase->handle(is_string($month) ? $month : null));
    }
}
