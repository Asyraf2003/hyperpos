<?php

declare(strict_types=1);

namespace App\Adapters\Out\ProductCatalog\Concerns;

use App\Ports\Out\ProductCatalog\ProductWriteConflictException;
use Illuminate\Database\QueryException;

trait TranslatesProductWriteConflicts
{
    private function translateProductWriteConflict(QueryException $exception): ProductWriteConflictException
    {
        if ($this->isDuplicateKodeBarangException($exception)) {
            return ProductWriteConflictException::duplicateKodeBarang($exception);
        }

        throw $exception;
    }

    private function isDuplicateKodeBarangException(QueryException $exception): bool
    {
        $sqlState = (string) ($exception->errorInfo[0] ?? $exception->getCode());
        $driverCode = (int) ($exception->errorInfo[1] ?? 0);
        $message = mb_strtolower($exception->getMessage());

        $looksLikeUniqueViolation = $sqlState === '23000'
            || $driverCode === 1062
            || str_contains($message, 'duplicate entry')
            || str_contains($message, 'unique constraint failed');

        if (! $looksLikeUniqueViolation) {
            return false;
        }

        return str_contains($message, 'products_kode_barang_active_unique')
            || str_contains($message, 'products_kode_barang_unique')
            || str_contains($message, 'products.kode_barang')
            || (str_contains($message, 'products') && str_contains($message, 'kode_barang'));
    }
}
