<?php

declare(strict_types=1);

namespace App\Ports\Out\ProductCatalog;

use RuntimeException;
use Throwable;

final class ProductWriteConflictException extends RuntimeException
{
    public const DUPLICATE_KODE_BARANG = 'duplicate_kode_barang';

    private function __construct(
        private readonly string $conflictCode,
        string $message,
        ?Throwable $previous = null,
    ) {
        parent::__construct($message, 0, $previous);
    }

    public static function duplicateKodeBarang(Throwable $previous): self
    {
        return new self(
            self::DUPLICATE_KODE_BARANG,
            'Kode barang sudah dipakai product lain.',
            $previous,
        );
    }

    public function conflictCode(): string
    {
        return $this->conflictCode;
    }
}
