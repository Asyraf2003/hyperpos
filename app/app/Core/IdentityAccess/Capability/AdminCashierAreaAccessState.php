<?php

declare(strict_types=1);

namespace App\Core\IdentityAccess\Capability;

use InvalidArgumentException;

final class AdminCashierAreaAccessState
{
    private const CAPABILITY_KEY = 'admin_cashier_area_access';

    private function __construct(
        private readonly string $actorId,
        private readonly bool $active,
    ) {
        if (trim($this->actorId) === '') {
            throw new InvalidArgumentException('Actor id must not be empty.');
        }
    }

    public static function active(string $actorId): self
    {
        return new self($actorId, true);
    }

    public static function inactive(string $actorId): self
    {
        return new self($actorId, false);
    }

    public function actorId(): string
    {
        return $this->actorId;
    }

    public function isActive(): bool
    {
        return $this->active === true;
    }

    public function isInactive(): bool
    {
        return $this->active === false;
    }

    public function capabilityKey(): string
    {
        return self::CAPABILITY_KEY;
    }
}