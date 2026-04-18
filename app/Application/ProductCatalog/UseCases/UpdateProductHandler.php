<?php

declare(strict_types=1);

namespace App\Application\ProductCatalog\UseCases;

use App\Application\ProductCatalog\UseCases\Concerns\HandlesProductWriteExceptions;
use App\Application\ProductCatalog\UseCases\Concerns\NormalizesProductMasterInput;
use App\Application\Shared\DTO\Result;
use App\Core\Shared\Exceptions\DomainException;
use App\Core\Shared\ValueObjects\Money;
use App\Ports\Out\ProductCatalog\ProductReaderPort;
use App\Ports\Out\ProductCatalog\ProductWriterPort;
use Illuminate\Database\QueryException;

final class UpdateProductHandler
{
    use HandlesProductWriteExceptions;
    use NormalizesProductMasterInput;

    public function __construct(
        private readonly ProductReaderPort $products,
        private readonly ProductWriterPort $writer,
        private readonly UpdateProductDuplicateGuard $duplicateGuard,
        private readonly UpdateProductSuccessPayloadBuilder $successPayloads,
    ) {
    }

    public function handle(
        string $productId,
        ?string $kodeBarang,
        string $namaBarang,
        string $merek,
        ?int $ukuran,
        int $hargaJual,
        ?int $reorderPointQty,
        ?int $criticalThresholdQty,
    ): Result {
        $product = $this->products->getById($productId);

        if ($product === null) {
            return Result::failure(
                'Product tidak ditemukan.',
                ['product' => ['PRODUCT_NOT_FOUND']]
            );
        }

        $normalizedKodeBarang = $this->normalizeKodeBarang($kodeBarang);
        $normalizedNamaBarang = trim($namaBarang);
        $normalizedMerek = trim($merek);

        $duplicateFailure = $this->duplicateGuard->ensureNoConflict(
            $productId,
            $normalizedKodeBarang,
            $normalizedNamaBarang,
            $normalizedMerek,
            $ukuran,
        );

        if ($duplicateFailure !== null) {
            return $duplicateFailure;
        }

        try {
            $product->updateMaster(
                $normalizedKodeBarang,
                $normalizedNamaBarang,
                $normalizedMerek,
                $ukuran,
                Money::fromInt($hargaJual),
                $reorderPointQty,
                $criticalThresholdQty,
            );
        } catch (DomainException $e) {
            return Result::failure(
                $e->getMessage(),
                ['product' => ['INVALID_PRODUCT']]
            );
        }

        try {
            $this->writer->update($product);
        } catch (QueryException $e) {
            $failure = $this->toProductWriteFailure($e);

            if ($failure !== null) {
                return $failure;
            }

            throw $e;
        }

        return Result::success(
            $this->successPayloads->build($product),
            'Product master berhasil diperbarui.'
        );
    }
}
