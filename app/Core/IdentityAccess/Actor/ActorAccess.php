<?php

declare(strict_types=1);

namespace App\Core\IdentityAccess\Actor;

use App\Core\IdentityAccess\Role\Role;
use InvalidArgumentException;

final class ActorAccess
{
    public function __construct(
        private readonly string $actorId,
        private readonly Role $role,
    ) {
        if (trim($this->actorId) === '') {
            throw new InvalidArgumentException('Actor id must not be empty.');
        }
    }

    public function actorId(): string
    {
        return $this->actorId;
    }

    public function role(): Role
    {
        return $this->role;
    }

    public function isAdmin(): bool
    {
        return $this->role->isAdmin();
    }

    public function isKasir(): bool
    {
        return $this->role->isKasir();
    }
}
