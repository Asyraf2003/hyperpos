<?php

declare(strict_types=1);

namespace App\Adapters\Out\Audit;

final class AuditJsonPayload
{
    /**
     * @return array<string, mixed>
     */
    public function decode(?string $json): array
    {
        if ($json === null || trim($json) === '') {
            return [];
        }

        $decoded = json_decode($json, true);

        return is_array($decoded) ? $decoded : [];
    }

    /**
     * @param array<string, mixed> $context
     */
    public function encodePretty(array $context): string
    {
        $json = json_encode($context, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

        return is_string($json) ? $json : '{}';
    }
}
