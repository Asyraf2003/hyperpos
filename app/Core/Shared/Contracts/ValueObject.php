<?php

declare(strict_types=1);

namespace App\Core\Shared\Contracts;

interface ValueObject
{
    public function equals(self $other): bool;
}
