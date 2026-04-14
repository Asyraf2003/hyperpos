<?php

declare(strict_types=1);

namespace App\Adapters\In\Http\Controllers\Admin;

use App\Application\Reporting\UseCases\GetAdminDashboardOverviewHandler;
use Illuminate\Contracts\View\View;
use Illuminate\Routing\Controller;

final class AdminDashboardPageController extends Controller
{
    public function __invoke(GetAdminDashboardOverviewHandler $useCase): View
    {
        return view('admin.dashboard.index', [
            'dashboard' => $useCase->handle(),
        ]);
    }
}
