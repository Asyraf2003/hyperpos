<?php

declare(strict_types=1);

namespace App\Application\Reporting\UseCases;

final class GetAdminDashboardPagePayloadHandler
{
    public function __construct(
        private readonly GetAdminDashboardOverviewHandler $overview,
    ) {
    }

    public function handle(?string $month = null): array
    {
        return $this->overview->handle($month);
    }
}
