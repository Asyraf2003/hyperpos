<?php

declare(strict_types=1);

namespace App\Adapters\In\Http\Controllers\Admin\Procurement\Support;

use App\Core\ProductCatalog\Product\Product;
use App\Ports\Out\ProductCatalog\ProductReaderPort;

final class EditSupplierInvoiceLineItemsViewBuilder
{
    public function __construct(
        private readonly ProductReaderPort $products,
    ) {
    }

    /**
     * @param list<array<string, mixed>> $existingLines
     * @return array<int, array<string, string|int>>
     */
    public function build(array $existingLines): array
    {
        $productLabelsById = [];

        foreach ($this->products->findAll() as $product) {
            $productLabelsById[$product->id()] = $this->buildProductLabel($product);
        }

        $oldLines = old('lines');

        if (! is_array($oldLines) || $oldLines === []) {
            $oldLines = array_map(
                static fn (array $line): array => [
                    'previous_line_id' => (string) ($line['id'] ?? ''),
                    'line_no' => (string) ($line['line_no'] ?? ''),
                    'product_id' => (string) ($line['product_id'] ?? ''),
                    'qty_pcs' => (string) ($line['qty_pcs'] ?? '1'),
                    'line_total_rupiah' => (string) ($line['line_total_rupiah'] ?? ''),
                ],
                $existingLines,
            );
        }

        if ($oldLines === []) {
            $oldLines = [[
                'previous_line_id' => '',
                'line_no' => '1',
                'product_id' => '',
                'qty_pcs' => '1',
                'line_total_rupiah' => '',
            ]];
        }

        $lineItems = [];

        foreach ($oldLines as $index => $line) {
            if (! is_array($line)) {
                continue;
            }

            $selectedProductId = (string) ($line['product_id'] ?? '');
            $lineTotalRaw = isset($line['line_total_rupiah']) ? (string) $line['line_total_rupiah'] : '';
            $lineNo = isset($line['line_no']) ? (string) $line['line_no'] : (string) ((int) $index + 1);

            $lineItems[] = [
                'index' => (int) $index,
                'previous_line_id' => (string) ($line['previous_line_id'] ?? ''),
                'line_no' => $lineNo,
                'selected_product_id' => $selectedProductId,
                'selected_label' => $selectedProductId !== ''
                    ? ($productLabelsById[$selectedProductId] ?? '')
                    : '',
                'qty_pcs' => (string) ($line['qty_pcs'] ?? '1'),
                'line_total_raw' => $lineTotalRaw,
                'line_total_display' => $lineTotalRaw !== ''
                    ? number_format((int) $lineTotalRaw, 0, ',', '.')
                    : '',
            ];
        }

        return $lineItems;
    }

    private function buildProductLabel(Product $product): string
    {
        $parts = [$product->namaBarang(), $product->merek()];

        if ($product->ukuran() !== null) {
            $parts[] = (string) $product->ukuran();
        }

        $label = implode(' - ', $parts);

        if ($product->kodeBarang() !== null) {
            $label .= ' (' . $product->kodeBarang() . ')';
        }

        return $label;
    }
}
