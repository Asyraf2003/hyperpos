<?php

declare(strict_types=1);

namespace App\Application\Reporting\UseCases;

final class GetAdminDashboardPagePayloadHandler
{
    public function __construct(
        private readonly GetAdminDashboardOverviewHandler $overview,
        private readonly GetAdminDashboardAnalyticsHandler $analytics,
    ) {
    }

    public function handle(): array
    {
        return array_merge(
            $this->overview->handle(),
            [
                'analytics' => $this->analytics->handle(),
            ],
        );
    }
}
