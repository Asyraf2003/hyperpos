<?php

declare(strict_types=1);

namespace App\Core\Procurement\Supplier;

use App\Core\Shared\Exceptions\DomainException;

final class Supplier
{
    private function __construct(
        private string $id,
        private string $namaPtPengirim,
        private string $namaPtPengirimNormalized,
    ) {
    }

    public static function create(
        string $id,
        string $namaPtPengirim,
    ): self {
        self::assertValid($id, $namaPtPengirim);

        return new self(
            trim($id),
            trim($namaPtPengirim),
            self::normalizeNamaPtPengirim($namaPtPengirim),
        );
    }

    public static function rehydrate(
        string $id,
        string $namaPtPengirim,
    ): self {
        self::assertValid($id, $namaPtPengirim);

        return new self(
            trim($id),
            trim($namaPtPengirim),
            self::normalizeNamaPtPengirim($namaPtPengirim),
        );
    }

    public function rename(string $namaPtPengirim): void
    {
        self::assertValid($this->id, $namaPtPengirim);

        $this->namaPtPengirim = trim($namaPtPengirim);
        $this->namaPtPengirimNormalized = self::normalizeNamaPtPengirim($namaPtPengirim);
    }

    public function id(): string
    {
        return $this->id;
    }

    public function namaPtPengirim(): string
    {
        return $this->namaPtPengirim;
    }

    public function namaPtPengirimNormalized(): string
    {
        return $this->namaPtPengirimNormalized;
    }

    private static function assertValid(
        string $id,
        string $namaPtPengirim,
    ): void {
        if (trim($id) === '') {
            throw new DomainException('Supplier id wajib ada.');
        }

        if (trim($namaPtPengirim) === '') {
            throw new DomainException('Nama PT pengirim wajib ada.');
        }
    }

    private static function normalizeNamaPtPengirim(string $namaPtPengirim): string
    {
        $normalized = trim($namaPtPengirim);
        $normalized = preg_replace('/\s+/', ' ', $normalized) ?? $normalized;

        return mb_strtolower($normalized);
    }
}
