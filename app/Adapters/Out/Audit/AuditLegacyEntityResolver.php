<?php

declare(strict_types=1);

namespace App\Adapters\Out\Audit;

final class AuditLegacyEntityResolver
{
    /**
     * @param array<string, mixed> $context
     */
    public function resolve(array $context): ?string
    {
        foreach ($this->candidateKeys() as $key) {
            $value = $this->nullableScalar($context[$key] ?? null);

            if ($value !== null) {
                return $value;
            }
        }

        return null;
    }

    /**
     * @return list<string>
     */
    private function candidateKeys(): array
    {
        return [
            'note_id',
            'supplier_invoice_id',
            'supplier_payment_id',
            'supplier_receipt_id',
            'product_id',
            'employee_id',
            'debt_id',
            'payroll_id',
        ];
    }

    private function nullableScalar(mixed $value): ?string
    {
        if (! is_scalar($value)) {
            return null;
        }

        $trimmed = trim((string) $value);

        return $trimmed === '' ? null : $trimmed;
    }
}
