<?php

declare(strict_types=1);

namespace App\Adapters\Out\Policy;

use App\Ports\Out\CapabilityPolicyPort;

final class NullCapabilityPolicyAdapter implements CapabilityPolicyPort
{
    /**
     * @param array<string, mixed> $context
     */
    public function can(string $capability, array $context = []): bool
    {
        return false;
    }
}
