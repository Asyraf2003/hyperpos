<?php

declare(strict_types=1);

namespace App\Adapters\In\Http\Middleware\IdentityAccess;

final class UiFeedbackMessageNormalizer
{
    /**
     * @param array<mixed> $messages
     * @return array<int,string>
     */
    public function normalizeMany(array $messages): array
    {
        $normalized = [];

        array_walk_recursive($messages, function (mixed $value) use (&$normalized): void {
            $message = $this->normalizeOne($value);

            if ($message !== null) {
                $normalized[] = $message;
            }
        });

        return array_values(array_unique($normalized));
    }

    public function normalizeOne(mixed $value): ?string
    {
        if (! is_scalar($value)) {
            return null;
        }

        $message = trim((string) $value);

        return $message === '' ? null : $message;
    }
}
