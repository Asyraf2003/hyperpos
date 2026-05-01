<?php

declare(strict_types=1);

namespace App\Adapters\Out\Audit;

final class AuditReasonResolver
{
    /**
     * @param array<string, mixed> $context
     */
    public function fromContext(array $context): string
    {
        foreach (['reason', 'alasan', 'void_reason', 'correction_reason', 'note', 'notes'] as $key) {
            $value = $context[$key] ?? null;

            if (is_scalar($value) && trim((string) $value) !== '') {
                return (string) $value;
            }
        }

        return '-';
    }

    public function fromScalar(mixed $reason): string
    {
        if (is_scalar($reason) && trim((string) $reason) !== '') {
            return (string) $reason;
        }

        return '-';
    }
}
