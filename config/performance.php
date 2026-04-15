<?php

declare(strict_types=1);

return [
    'admin_dashboard_overview_cache_ttl_seconds' => (int) env(
        'ADMIN_DASHBOARD_OVERVIEW_CACHE_TTL_SECONDS',
        30
    ),
];
