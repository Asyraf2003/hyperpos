<?php

declare(strict_types=1);

namespace App\Adapters\Out\Note\Queries;

use DateTimeImmutable;

final class CashierNoteHistoryCriteria
{
    public function __construct(
        public readonly string $anchorDateText,
        public readonly string $previousDateText,
        public readonly string $search,
        public readonly string $paymentStatus,
        public readonly string $workStatus,
        public readonly int $page,
        public readonly int $perPage,
    ) {
    }

    /**
     * @param array<string, mixed> $filters
     */
    public static function fromFilters(array $filters): self
    {
        $anchorDate = self::resolveAnchorDate($filters['date'] ?? null);

        return new self(
            anchorDateText: $anchorDate->format('Y-m-d'),
            previousDateText: $anchorDate->modify('-1 day')->format('Y-m-d'),
            search: self::normalizeString($filters['search'] ?? null),
            paymentStatus: self::normalizeString($filters['payment_status'] ?? null),
            workStatus: self::normalizeString($filters['work_status'] ?? null),
            page: max((int) ($filters['page'] ?? 1), 1),
            perPage: 10,
        );
    }

    private static function resolveAnchorDate(mixed $value): DateTimeImmutable
    {
        if (! is_string($value)) {
            return new DateTimeImmutable(date('Y-m-d'));
        }

        $trimmed = trim($value);
        $parsed = DateTimeImmutable::createFromFormat('!Y-m-d', $trimmed);

        if ($parsed === false || $parsed->format('Y-m-d') !== $trimmed) {
            return new DateTimeImmutable(date('Y-m-d'));
        }

        return $parsed;
    }

    private static function normalizeString(mixed $value): string
    {
        return is_string($value) ? trim($value) : '';
    }
}
