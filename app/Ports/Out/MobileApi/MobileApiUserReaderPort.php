<?php

declare(strict_types=1);

namespace App\Ports\Out\MobileApi;

use App\Application\MobileApi\Auth\DTO\MobileApiAuthenticatedUser;

interface MobileApiUserReaderPort
{
    public function findById(string $userId): ?MobileApiAuthenticatedUser;
}
