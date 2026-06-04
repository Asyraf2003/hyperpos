<?php

declare(strict_types=1);

namespace App\Core\ServiceCatalog;

use App\Core\Shared\Exceptions\DomainException;

final class ServiceCatalogItem
{
    private function __construct(
        private readonly string $id,
        private readonly string $name,
        private readonly string $normalizedName,
        private readonly int $defaultPriceRupiah,
        private readonly bool $active,
    ) {
    }

    public static function rehydrate(
        string $id,
        string $name,
        string $normalizedName,
        int $defaultPriceRupiah,
        bool $active,
    ): self {
        if (trim($id) === '' || trim($name) === '' || trim($normalizedName) === '') {
            throw new DomainException('Data master jasa tidak valid.');
        }

        if ($defaultPriceRupiah <= 0) {
            throw new DomainException('Harga default jasa wajib lebih dari 0.');
        }

        return new self($id, trim($name), trim($normalizedName), $defaultPriceRupiah, $active);
    }

    public function id(): string { return $this->id; }
    public function name(): string { return $this->name; }
    public function normalizedName(): string { return $this->normalizedName; }
    public function defaultPriceRupiah(): int { return $this->defaultPriceRupiah; }
    public function active(): bool { return $this->active; }
}
