<?php

declare(strict_types=1);

namespace App\Application\MobileApi\Auth\DTO;

final readonly class MobileApiActorResolution
{
    private function __construct(
        public ?MobileApiActor $actor,
        public string $status,
    ) {
    }

    public static function resolved(MobileApiActor $actor): self
    {
        return new self($actor, 'resolved');
    }

    public static function missingUser(): self
    {
        return new self(null, 'missing_user');
    }

    public static function unknown(): self
    {
        return new self(null, 'unknown');
    }

    public static function unsupported(): self
    {
        return new self(null, 'unsupported');
    }

    public function isResolved(): bool
    {
        return $this->actor !== null && $this->status === 'resolved';
    }
}
