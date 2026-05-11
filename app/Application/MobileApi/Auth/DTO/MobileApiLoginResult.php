<?php

declare(strict_types=1);

namespace App\Application\MobileApi\Auth\DTO;

final readonly class MobileApiLoginResult
{
    /**
     * @param array<string, list<string>>|null $errors
     */
    private function __construct(
        public bool $success,
        public int $status,
        public ?string $message,
        public ?array $errors,
        public ?MobileApiIssuedToken $token,
        public ?MobileApiActor $actor,
    ) {
    }

    public static function success(MobileApiIssuedToken $token, MobileApiActor $actor): self
    {
        return new self(true, 200, null, null, $token, $actor);
    }

    public static function invalidCredentials(): self
    {
        return new self(false, 422, 'Email atau password tidak valid.', [
            'email' => ['AUTH_FAILED'],
        ], null, null);
    }

    public static function actorUnknown(): self
    {
        return new self(false, 403, 'Aktor tidak dikenali.', [
            'actor' => ['ACTOR_UNKNOWN'],
        ], null, null);
    }

    public static function actorUnsupported(): self
    {
        return new self(false, 403, 'Role aktor tidak didukung.', [
            'actor' => ['ACTOR_UNSUPPORTED'],
        ], null, null);
    }
}
