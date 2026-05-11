<?php

declare(strict_types=1);

namespace App\Application\MobileApi\Auth\DTO;

final readonly class MobileApiAuthenticatedUser
{
    public function __construct(
        public string $id,
        public string $name,
        public string $email,
    ) {
    }
}
