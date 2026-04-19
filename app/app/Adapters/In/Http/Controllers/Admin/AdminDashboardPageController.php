<?php

declare(strict_types=1);

namespace App\Adapters\In\Http\Controllers\Admin;

use App\Application\Reporting\UseCases\GetAdminDashboardPagePayloadHandler;
use Illuminate\Contracts\View\View;
use Illuminate\Routing\Controller;

final class AdminDashboardPageController extends Controller
{
    public function __invoke(GetAdminDashboardPagePayloadHandler $useCase): View
    {
        return view('admin.dashboard.index', [
            'dashboard' => $useCase->handle(),
        ]);
    }
}
