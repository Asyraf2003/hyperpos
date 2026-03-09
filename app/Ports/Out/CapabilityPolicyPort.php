<?php

declare(strict_types=1);

namespace App\Ports\Out;

interface CapabilityPolicyPort
{
    /**
     * @param array<string, mixed> $context
     */
    public function can(string $capability, array $context = []): bool;
}
