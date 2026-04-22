<?php

declare(strict_types=1);

namespace App\Adapters\Out\Note;

final class DbNoteRevisionPayloadCodec
{
    /**
     * @param array<string, mixed> $payload
     */
    public function encode(array $payload): string
    {
        $encoded = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        return $encoded === false ? '{}' : $encoded;
    }

    /**
     * @return array<string, mixed>
     */
    public function decode(mixed $payload): array
    {
        if (! is_string($payload) || trim($payload) === '') {
            return [];
        }

        $decoded = json_decode($payload, true);

        return is_array($decoded) ? $decoded : [];
    }
}
