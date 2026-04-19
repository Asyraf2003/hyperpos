<?php

declare(strict_types=1);

namespace App\Adapters\Out\EmployeeFinance;

final class EmployeeDetailSnapshotReader
{
    public function decode(string $json): array
    {
        $decoded = json_decode($json, true, 512, JSON_THROW_ON_ERROR);

        return is_array($decoded) ? $decoded : [];
    }

    public function nullableInt(array $snapshot, string $key): ?int
    {
        if (!array_key_exists($key, $snapshot) || $snapshot[$key] === null) {
            return null;
        }

        return (int) $snapshot[$key];
    }

    public function nullableString(array $snapshot, string $key): ?string
    {
        if (!array_key_exists($key, $snapshot) || $snapshot[$key] === null) {
            return null;
        }

        return (string) $snapshot[$key];
    }

    public function stringOr(array $snapshot, string $key, string $fallback): string
    {
        return $this->nullableString($snapshot, $key) ?? $fallback;
    }
}
