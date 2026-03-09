<?php

declare(strict_types=1);

namespace App\Ports\In;

use App\Application\Shared\DTO\Result;

interface HealthCheckUseCase
{
    public function execute(): Result;
}
