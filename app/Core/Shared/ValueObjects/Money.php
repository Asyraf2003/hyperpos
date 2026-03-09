<?php

declare(strict_types=1);

namespace App\Core\Shared\ValueObjects;

use App\Core\Shared\Contracts\ValueObject;
use App\Core\Shared\Exceptions\DomainException;

final class Money implements ValueObject
{
    private function __construct(
        private readonly int $amount
    ) {
    }

    public static function fromInt(int $amount): self
    {
        return new self($amount);
    }

    public static function zero(): self
    {
        return new self(0);
    }

    public function amount(): int
    {
        return $this->amount;
    }

    public function add(self $other): self
    {
        return new self($this->amount + $other->amount);
    }

    public function subtract(self $other): self
    {
        return new self($this->amount - $other->amount);
    }

    public function multiply(int $multiplier): self
    {
        return new self($this->amount * $multiplier);
    }

    public function ensureNotNegative(string $message = 'Money must not be negative.'): void
    {
        if ($this->amount < 0) {
            throw new DomainException($message);
        }
    }

    public function isZero(): bool
    {
        return $this->amount === 0;
    }

    public function isNegative(): bool
    {
        return $this->amount < 0;
    }

    public function greaterThan(self $other): bool
    {
        return $this->amount > $other->amount;
    }

    public function greaterThanOrEqual(self $other): bool
    {
        return $this->amount >= $other->amount;
    }

    public function equals(ValueObject $other): bool
    {
        return $other instanceof self
            && $this->amount === $other->amount;
    }
}
