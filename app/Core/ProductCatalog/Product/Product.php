<?php

declare(strict_types=1);

namespace App\Core\ProductCatalog\Product;

use App\Core\Shared\Exceptions\DomainException;
use App\Core\Shared\ValueObjects\Money;

final class Product
{
    private function __construct(
        private string $id,
        private ?string $kodeBarang,
        private string $namaBarang,
        private string $merek,
        private ?int $ukuran,
        private Money $hargaJual,
    ) {
    }

    public static function create(
        string $id,
        ?string $kodeBarang,
        string $namaBarang,
        string $merek,
        ?int $ukuran,
        Money $hargaJual,
    ): self {
        self::assertValid($id, $namaBarang, $merek, $hargaJual);

        return new self(
            $id,
            self::normalizeKodeBarang($kodeBarang),
            trim($namaBarang),
            trim($merek),
            $ukuran,
            $hargaJual,
        );
    }

    public static function rehydrate(
        string $id,
        ?string $kodeBarang,
        string $namaBarang,
        string $merek,
        ?int $ukuran,
        Money $hargaJual,
    ): self {
        self::assertValid($id, $namaBarang, $merek, $hargaJual);

        return new self(
            $id,
            self::normalizeKodeBarang($kodeBarang),
            trim($namaBarang),
            trim($merek),
            $ukuran,
            $hargaJual,
        );
    }

    public function updateMaster(
        ?string $kodeBarang,
        string $namaBarang,
        string $merek,
        ?int $ukuran,
        Money $hargaJual,
    ): void {
        self::assertValid($this->id, $namaBarang, $merek, $hargaJual);

        $this->kodeBarang = self::normalizeKodeBarang($kodeBarang);
        $this->namaBarang = trim($namaBarang);
        $this->merek = trim($merek);
        $this->ukuran = $ukuran;
        $this->hargaJual = $hargaJual;
    }

    public function id(): string
    {
        return $this->id;
    }

    public function kodeBarang(): ?string
    {
        return $this->kodeBarang;
    }

    public function namaBarang(): string
    {
        return $this->namaBarang;
    }

    public function merek(): string
    {
        return $this->merek;
    }

    public function ukuran(): ?int
    {
        return $this->ukuran;
    }

    public function hargaJual(): Money
    {
        return $this->hargaJual;
    }

    private static function assertValid(
        string $id,
        string $namaBarang,
        string $merek,
        Money $hargaJual,
    ): void {
        if (trim($id) === '') {
            throw new DomainException('Product id wajib ada.');
        }

        if (trim($namaBarang) === '') {
            throw new DomainException('Nama barang wajib ada.');
        }

        if (trim($merek) === '') {
            throw new DomainException('Merek wajib ada.');
        }

        if ($hargaJual->greaterThan(Money::zero()) === false) {
            throw new DomainException('Harga jual harus lebih besar dari nol.');
        }
    }

    private static function normalizeKodeBarang(?string $kodeBarang): ?string
    {
        if ($kodeBarang === null) {
            return null;
        }

        $normalized = trim($kodeBarang);

        return $normalized === '' ? null : $normalized;
    }
}
