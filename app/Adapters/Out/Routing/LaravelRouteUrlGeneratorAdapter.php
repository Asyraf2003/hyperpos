<?php

declare(strict_types=1);

namespace App\Adapters\Out\Routing;

use App\Ports\Out\RouteUrlGeneratorPort;
use Illuminate\Contracts\Routing\UrlGenerator;

final readonly class LaravelRouteUrlGeneratorAdapter implements RouteUrlGeneratorPort
{
    public function __construct(
        private UrlGenerator $urls,
    ) {
    }

    /**
     * @param array<string, mixed> $parameters
     */
    public function route(string $name, array $parameters = [], bool $absolute = true): string
    {
        return $this->urls->route($name, $parameters, $absolute);
    }
}
