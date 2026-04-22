<?php

declare(strict_types=1);

namespace App\Application\Note\Services\RevisionWorkspace;

use App\Core\Shared\Exceptions\DomainException;
use App\Ports\Out\ProductCatalog\ProductReaderPort;

final class RevisionWorkspaceProductLineMapper
{
    public function __construct(
        private readonly ProductReaderPort $products,
    ) {
    }

    /**
     * @param array<string, mixed> $storeLine
     * @return array<string, mixed>
     */
    public function map(array $storeLine, int $fallbackSubtotal = 0): array
    {
        $qty = max((int) ($storeLine['qty'] ?? 1), 1);
        $lineTotal = (int) ($storeLine['line_total_rupiah'] ?? $storeLine['subtotal_rupiah'] ?? $fallbackSubtotal);
        $unitPrice = (int) ($storeLine['selling_price_rupiah'] ?? ($lineTotal > 0 ? intdiv($lineTotal, $qty) : 0));

        $productId = (string) ($storeLine['product_id'] ?? '');
        $selectedLabel = '';

        if ($productId !== '') {
            $product = $this->products->getById($productId);
            $selectedLabel = $product?->namaBarang() ?? '';
        }

        return [
            'product_id' => $productId,
            'qty' => $qty,
            'unit_price_rupiah' => $unitPrice,
            'selected_label' => $selectedLabel,
        ];
    }

    /**
     * @param array<string, mixed> $payload
     * @return array<string, mixed>
     */
    public function singleStoreLine(array $payload, string $message): array
    {
        $storeLines = is_array($payload['store_stock_lines'] ?? null) ? $payload['store_stock_lines'] : [];

        if (count($storeLines) !== 1 || ! is_array($storeLines[0])) {
            throw new DomainException($message);
        }

        return $storeLines[0];
    }
}
