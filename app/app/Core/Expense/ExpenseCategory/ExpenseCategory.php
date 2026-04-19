<?php

declare(strict_types=1);

namespace App\Core\Expense\ExpenseCategory;

final class ExpenseCategory
{
    use ExpenseCategoryValidation;

    private function __construct(
        private string $id,
        private string $code,
        private string $name,
        private ?string $description,
        private bool $isActive,
    ) {
    }

    public static function create(string $id, string $code, string $name, ?string $description = null): self
    {
        self::assertValid($id, $code, $name);

        return new self(trim($id), trim($code), trim($name), self::normalizeDescription($description), true);
    }

    public static function rehydrate(string $id, string $code, string $name, ?string $description, bool $isActive): self
    {
        self::assertValid($id, $code, $name);

        return new self(trim($id), trim($code), trim($name), self::normalizeDescription($description), $isActive);
    }

    public function update(string $code, string $name, ?string $description): void
    {
        self::assertValid($this->id, $code, $name);

        $this->code = trim($code);
        $this->name = trim($name);
        $this->description = self::normalizeDescription($description);
    }

    public function deactivate(): void { $this->isActive = false; }
    public function activate(): void { $this->isActive = true; }

    public function id(): string { return $this->id; }
    public function code(): string { return $this->code; }
    public function name(): string { return $this->name; }
    public function description(): ?string { return $this->description; }
    public function isActive(): bool { return $this->isActive; }
}
