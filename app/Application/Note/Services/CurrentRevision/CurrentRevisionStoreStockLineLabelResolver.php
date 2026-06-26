<?php

declare(strict_types=1);

namespace App\Application\Note\Services\CurrentRevision;

use App\Ports\Out\ProductCatalog\ProductReaderPort;

final class CurrentRevisionStoreStockLineLabelResolver
{
    public function __construct(
        private readonly ProductReaderPort $products,
    ) {
    }

    /** @param array<string, mixed> $payload */
    public function primary(array $payload): string
    {
        $lines = $this->lines($payload);
        if ($lines === []) {
            return 'Produk';
        }

        $label = $this->label($lines[0]);
        $remaining = count($lines) - 1;

        return $remaining > 0 ? $label . ' +' . $remaining . ' item' : $label;
    }

    /** @param array<string, mixed> $payload */
    public function summary(array $payload): ?string
    {
        $lines = $this->lines($payload);
        if ($lines === []) {
            return null;
        }

        return implode(' • ', array_map(
            fn (array $line): string => $this->label($line) . ' x' . (int) ($line['qty'] ?? 0),
            $lines,
        ));
    }

    /** @param array<string, mixed> $payload */
    private function lines(array $payload): array
    {
        return is_array($payload['store_stock_lines'] ?? null)
            ? array_values(array_filter($payload['store_stock_lines'], 'is_array'))
            : [];
    }

    /** @param array<string, mixed> $line */
    private function label(array $line): string
    {
        $productId = trim((string) ($line['product_id'] ?? ''));
        if ($productId === '') {
            return 'Produk';
        }

        $product = $this->products->getById($productId);
        $names = $product !== null ? [$productId => $product->namaBarang()] : [];

        return CurrentRevisionPackageProductNameResolver::displayName($line, $productId, $names);
    }
}
