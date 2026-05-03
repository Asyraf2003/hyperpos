<?php

declare(strict_types=1);

namespace App\Adapters\In\Http\ViewData\Admin;

use Illuminate\Support\Carbon;

final class AdminDashboardFilterDrawerViewData
{
    /**
     * @param array<string, mixed> $dashboard
     * @return array{
     *     form_id: string,
     *     open_button_id: string,
     *     close_button_id: string,
     *     drawer_id: string,
     *     backdrop_id: string,
     *     active_month: string
     * }
     */
    public static function fromDashboard(array $dashboard): array
    {
        $activeMonth = $dashboard['period']['active_month'] ?? Carbon::now()->format('Y-m');

        return [
            'form_id' => 'admin-dashboard-filter',
            'open_button_id' => 'admin-dashboard-filter-open-filter',
            'close_button_id' => 'admin-dashboard-filter-close-filter',
            'drawer_id' => 'admin-dashboard-filter-drawer',
            'backdrop_id' => 'admin-dashboard-filter-backdrop',
            'active_month' => is_string($activeMonth) ? $activeMonth : Carbon::now()->format('Y-m'),
        ];
    }
}
