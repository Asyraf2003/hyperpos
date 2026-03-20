<?php

declare(strict_types=1);

namespace App\Adapters\In\Http\Controllers\Admin\Procurement;

use App\Core\ProductCatalog\Product\Product;
use App\Ports\Out\ProductCatalog\ProductReaderPort;
use Illuminate\Contracts\View\View;
use Illuminate\Routing\Controller;

final class CreateSupplierInvoicePageController extends Controller
{
    public function __invoke(ProductReaderPort $products): View
    {
        return view('admin.procurement.supplier_invoices.create', [
            'lineItemsView' => $this->buildLineItemsView($products->findAll()),
        ]);
    }

    /**
     * @param array<int, Product> $products
     * @return array<int, array<string, string|int>>
     */
    private function buildLineItemsView(array $products): array
    {
        $productLabelsById = [];

        foreach ($products as $product) {
            $productLabelsById[$product->id()] = $this->buildProductLabel($product);
        }

        $oldLines = old('lines');

        if (! is_array($oldLines) || $oldLines === []) {
            $oldLines = [['product_id' => '', 'qty_pcs' => '1', 'line_total_rupiah' => '']];
        }

        $lineItems = [];

        foreach ($oldLines as $index => $line) {
            if (! is_array($line)) {
                continue;
            }

            $selectedProductId = (string) ($line['product_id'] ?? '');
            $lineTotalRaw = isset($line['line_total_rupiah']) ? (string) $line['line_total_rupiah'] : '';

            $lineItems[] = [
                'index' => (int) $index,
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
