<?php

declare(strict_types=1);

namespace App\Application\EmployeeFinance\DTO;

final class EmployeeTableQuery
{
    public function __construct(
        private readonly ?string $q,
        private readonly int $page,
        private readonly int $perPage,
        private readonly string $sortBy,
        private readonly string $sortDir,
    ) {
    }

    public static function fromValidated(array $data): self
    {
        return new self(
            self::nullableString($data['q'] ?? null),
            isset($data['page']) ? (int) $data['page'] : 1,
            isset($data['per_page']) ? (int) $data['per_page'] : 10,
            isset($data['sort_by']) ? (string) $data['sort_by'] : 'employee_name',
            isset($data['sort_dir']) ? (string) $data['sort_dir'] : 'asc',
        );
    }

    public function q(): ?string
    {
        return $this->q;
    }

    public function page(): int
    {
        return $this->page;
    }

    public function perPage(): int
    {
        return $this->perPage;
    }

    public function sortBy(): string
    {
        return $this->sortBy;
    }

    public function sortDir(): string
    {
        return $this->sortDir;
    }

    private static function nullableString(mixed $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $normalized = trim($value);

        return $normalized === '' ? null : $normalized;
    }
}
