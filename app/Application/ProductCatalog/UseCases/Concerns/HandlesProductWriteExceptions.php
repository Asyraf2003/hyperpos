<?php

declare(strict_types=1);

namespace App\Application\ProductCatalog\UseCases\Concerns;

use App\Application\Shared\DTO\Result;
use Illuminate\Database\QueryException;

trait HandlesProductWriteExceptions
{
    private function toProductWriteFailure(QueryException $exception): ?Result
    {
        if (! $this->isDuplicateKodeBarangException($exception)) {
            return null;
        }

        return Result::failure(
            'Kode barang sudah dipakai product lain.',
            ['product' => ['PRODUCT_CODE_ALREADY_EXISTS']]
        );
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
