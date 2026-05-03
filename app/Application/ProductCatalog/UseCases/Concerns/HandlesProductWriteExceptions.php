<?php

declare(strict_types=1);

namespace App\Application\ProductCatalog\UseCases\Concerns;

use App\Application\Shared\DTO\Result;
use App\Ports\Out\ProductCatalog\ProductWriteConflictException;

trait HandlesProductWriteExceptions
{
    private function toProductWriteFailure(ProductWriteConflictException $exception): ?Result
    {
        if ($exception->conflictCode() !== ProductWriteConflictException::DUPLICATE_KODE_BARANG) {
            return null;
        }

        return Result::failure(
            'Kode barang sudah dipakai product lain.',
            ['product' => ['PRODUCT_CODE_ALREADY_EXISTS']]
        );
    }
}
