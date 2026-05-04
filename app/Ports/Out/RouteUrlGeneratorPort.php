<?php

declare(strict_types=1);

namespace App\Ports\Out;

interface RouteUrlGeneratorPort
{
    /**
     * @param array<string, mixed> $parameters
     */
    public function route(string $name, array $parameters = [], bool $absolute = true): string;
}
