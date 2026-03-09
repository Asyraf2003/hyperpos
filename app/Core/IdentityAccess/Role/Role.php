<?php

declare(strict_types=1);

namespace App\Core\IdentityAccess\Role;

use InvalidArgumentException;

final class Role
{
    public const ADMIN = 'admin';
    public const KASIR = 'kasir';

    private function __construct(
        private readonly string $value,
    ) {
    }

    public static function admin(): self
    {
        return new self(self::ADMIN);
    }

    public static function kasir(): self
    {
        return new self(self::KASIR);
    }

    public static function fromString(string $value): self
    {
        return match ($value) {
            self::ADMIN => self::admin(),
            self::KASIR => self::kasir(),
            default => throw new InvalidArgumentException('Unsupported role value.'),
        };
    }

    public function value(): string
    {
        return $this->value;
    }

    public function isAdmin(): bool
    {
        return $this->value === self::ADMIN;
    }

    public function isKasir(): bool
    {
        return $this->value === self::KASIR;
    }
}
