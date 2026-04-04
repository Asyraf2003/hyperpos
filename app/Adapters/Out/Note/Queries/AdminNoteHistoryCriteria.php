<?php

declare(strict_types=1);

namespace App\Adapters\Out\Note\Queries;

final class AdminNoteHistoryCriteria
{
    public function __construct(
        public readonly string $dateFromText,
        public readonly string $dateToText,
        public readonly string $search,
        public readonly string $paymentStatus,
        public readonly string $editability,
        public readonly string $workSummary,
        public readonly int $page,
        public readonly int $perPage,
    ) {
    }

    /**
     * @param array<string, mixed> $filters
     */
    public static function fromFilters(array $filters): self
    {
        return new self(
            self::resolveDate($filters, 'date_from'),
            self::resolveDate($filters, 'date_to'),
            self::resolveString($filters, 'search'),
            self::resolveString($filters, 'payment_status'),
            self::resolveString($filters, 'editability'),
            self::resolveString($filters, 'work_summary'),
            self::resolvePositiveInt($filters, 'page', 1),
            self::resolvePositiveInt($filters, 'per_page', 10),
        );
    }

    /**
     * @param array<string, mixed> $filters
     */
    private static function resolveDate(array $filters, string $key): string
    {
        $value = $filters[$key] ?? null;

        if (! is_string($value)) {
            return date('Y-m-d');
        }

        $trimmed = trim($value);

        return $trimmed === '' ? date('Y-m-d') : $trimmed;
    }

    /**
     * @param array<string, mixed> $filters
     */
    private static function resolveString(array $filters, string $key): string
    {
        $value = $filters[$key] ?? null;

        if (! is_string($value)) {
            return '';
        }

        return trim($value);
    }

    /**
     * @param array<string, mixed> $filters
     */
    private static function resolvePositiveInt(array $filters, string $key, int $default): int
    {
        $value = $filters[$key] ?? null;

        if (! is_numeric($value)) {
            return $default;
        }

        $number = (int) $value;

        return $number > 0 ? $number : $default;
    }
}
