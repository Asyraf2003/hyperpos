<?php

declare(strict_types=1);

namespace App\Application\Shared\DTO;

final class Result
{
    private function __construct(
        private readonly bool $success,
        private readonly mixed $data = null,
        private readonly ?string $message = null,
        private readonly array $errors = [],
    ) {
    }

    public static function success(mixed $data = null, ?string $message = null): self
    {
        return new self(true, $data, $message, []);
    }

    public static function failure(?string $message = null, array $errors = []): self
    {
        return new self(false, null, $message, $errors);
    }

    public function isSuccess(): bool
    {
        return $this->success;
    }

    public function isFailure(): bool
    {
        return $this->success === false;
    }

    public function data(): mixed
    {
        return $this->data;
    }

    public function message(): ?string
    {
        return $this->message;
    }

    public function errors(): array
    {
        return $this->errors;
    }

    public function toArray(): array
    {
        return [
            'success' => $this->success,
            'data' => $this->data,
            'message' => $this->message,
            'errors' => $this->errors,
        ];
    }
}
