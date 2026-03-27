<?php

declare(strict_types=1);

namespace App\Adapters\In\Http\Requests\Note;

final class CreateNoteRowInputNormalizer
{
    /**
     * @return list<array<string, mixed>>
     */
    public function normalizeRows(mixed $rows): array
    {
        if (! is_array($rows)) {
            return [];
        }

        return array_map(fn ($row): array => $this->normalizeRow($row), $rows);
    }

    /**
     * @return array<string, mixed>
     */
    private function normalizeRow(mixed $row): array
    {
        if (! is_array($row)) {
            $row = [];
        }

        return [
            'line_type' => $this->trimOrNull($row['line_type'] ?? null),
            'product_id' => $this->trimOrNull($row['product_id'] ?? null),
            'qty' => $this->toNullableInt($row['qty'] ?? null),
            'service_name' => $this->trimOrNull($row['service_name'] ?? null),
            'service_price_rupiah' => $this->toNullableInt($row['service_price_rupiah'] ?? null),
            'service_notes' => $this->trimOrNull($row['service_notes'] ?? null),
        ];
    }

    private function trimOrNull(mixed $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $trimmed = trim($value);

        return $trimmed === '' ? null : $trimmed;
    }

    private function toNullableInt(mixed $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_int($value)) {
            return $value;
        }

        if (! is_string($value)) {
            return null;
        }

        $trimmed = trim($value);

        if ($trimmed === '' || ! preg_match('/^-?\d+$/', $trimmed)) {
            return null;
        }

        return (int) $trimmed;
    }
}
