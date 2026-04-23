<?php

declare(strict_types=1);

namespace App\Application\Note\Services;

use App\Core\ProductCatalog\Product\Product;
use App\Ports\Out\ProductCatalog\ProductReaderPort;

final class RefundImpactProductLabelResolver
{
    public function __construct(
        private readonly ProductReaderPort $products,
    ) {
    }

    public function resolve(string $productId): string
    {
        $id = trim($productId);

        if ($id === '') {
            return '';
        }

        $product = $this->products->getById($id);

        if ($product === null) {
            return $id;
        }

        return $this->buildLabel($product);
    }

    private function buildLabel(Product $product): string
    {
        $parts = [$product->namaBarang(), $product->merek()];

        if ($product->ukuran() !== null) {
            $parts[] = (string) $product->ukuran();
        }

        $label = implode(' — ', array_filter($parts, static fn (string $value): bool => trim($value) !== ''));

        if ($product->kodeBarang() !== null) {
            $label .= ' (' . $product->kodeBarang() . ')';
        }

        return trim($label) !== '' ? $label : $product->id();
    }
}
