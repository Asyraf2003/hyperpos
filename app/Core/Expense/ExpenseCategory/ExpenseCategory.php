<?php

declare(strict_types=1);

namespace App\Core\Expense\ExpenseCategory;

use App\Core\Shared\Exceptions\DomainException;

final class ExpenseCategory
{
    private function __construct(
        private string $id,
        private string $code,
        private string $name,
        private ?string $description,
        private bool $isActive,
    ) {
    }

    public static function create(
        string $id,
        string $code,
        string $name,
        ?string $description = null,
    ): self {
        self::assertValid($id, $code, $name);

        return new self(
            trim($id),
            trim($code),
            trim($name),
            self::normalizeDescription($description),
            true,
        );
    }

    public static function rehydrate(
        string $id,
        string $code,
        string $name,
        ?string $description,
        bool $isActive,
    ): self {
        self::assertValid($id, $code, $name);

        return new self(
            trim($id),
            trim($code),
            trim($name),
            self::normalizeDescription($description),
            $isActive,
        );
    }

    public function deactivate(): void
    {
        $this->isActive = false;
    }

    public function activate(): void
    {
        $this->isActive = true;
    }

    public function id(): string
    {
        return $this->id;
    }

    public function code(): string
    {
        return $this->code;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function description(): ?string
    {
        return $this->description;
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    private static function assertValid(
        string $id,
        string $code,
        string $name,
    ): void {
        if (trim($id) === '') {
            throw new DomainException('Expense category id wajib ada.');
        }

        if (trim($code) === '') {
            throw new DomainException('Kode expense category wajib ada.');
        }

        if (trim($name) === '') {
            throw new DomainException('Nama expense category wajib ada.');
        }
    }

    private static function normalizeDescription(?string $description): ?string
    {
        $normalized = trim((string) $description);

        return $normalized === '' ? null : $normalized;
    }
}
