<?php

declare(strict_types=1);

namespace App\Ports\Out\MobileApi;

use App\Application\MobileApi\Auth\DTO\MobileApiAuthenticatedUser;

interface MobileApiCredentialVerifierPort
{
    public function verify(string $email, string $password): ?MobileApiAuthenticatedUser;
}
