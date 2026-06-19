<?php

declare(strict_types=1);

namespace App\Application\Note\Services;

use App\Core\Shared\Exceptions\DomainException;

trait CreateTransactionWorkspaceWorkItemPayloadMapperValidation
{
    private function requiredString(mixed $value, string $message): string
    {
        if (! is_string($value) || trim($value) === '') {
            throw new DomainException($message);
        }

        return trim($value);
    }

    private function optionalNonNegativeInt(mixed $value): int
    {
        return is_int($value) && $value > 0 ? $value : 0;
    }

    private function optionalNullableNonNegativeInt(mixed $value): ?int
    {
        if ($value === null) {
            return null;
        }

        return is_int($value) && $value >= 0 ? $value : null;
    }

    /** @param array<string, mixed> $item */
    private function requiredServicePrice(array $item): int
    {
        $value = $item['service']['price_rupiah'] ?? null;

        if (! is_int($value)) {
            throw new DomainException('Harga servis wajib diisi.');
        }

        if ($value > 0) {
            return $value;
        }

        if ($value === 0 && ($item['pricing_mode'] ?? null) === 'package_auto_split') {
            return 0;
        }

        throw new DomainException('Harga servis wajib diisi.');
    }
}
