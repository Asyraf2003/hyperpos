<?php

declare(strict_types=1);

namespace App\Adapters\In\Http\Controllers\Admin;

use App\Application\Reporting\UseCases\GetAdminDashboardPagePayloadHandler;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

final class AdminDashboardPageController extends Controller
{
    public function __invoke(Request $request, GetAdminDashboardPagePayloadHandler $useCase): View
    {
        $month = $request->query('month');

        return view('admin.dashboard.index', [
            'dashboard' => $useCase->handle(is_string($month) ? $month : null),
        ]);
    }
}
