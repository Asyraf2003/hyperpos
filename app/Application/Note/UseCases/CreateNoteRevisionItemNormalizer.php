<?php

declare(strict_types=1);

namespace App\Application\Note\UseCases;

final class CreateNoteRevisionItemNormalizer
{
    public function integer(mixed $value, int $default = 0): int
    {
        return (int) preg_replace('/\D+/', '', (string) ($value ?? (string) $default));
    }

    public function positiveInteger(mixed $value, int $default = 1): int
    {
        return max($this->integer($value, $default), 1);
    }

    public function string(mixed $value, string $default = ''): string
    {
        return trim((string) ($value ?? $default));
    }
}
