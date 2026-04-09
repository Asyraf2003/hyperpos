<?php

declare(strict_types=1);

namespace App\Adapters\In\Http\Controllers\Admin\Procurement;

use App\Application\Procurement\UseCases\GetProcurementInvoiceDetailHandler;
use App\Core\ProductCatalog\Product\Product;
use App\Ports\Out\ProductCatalog\ProductReaderPort;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;

final class EditSupplierInvoicePageController extends Controller
{
    public function __construct(
        private readonly GetProcurementInvoiceDetailHandler $details,
        private readonly ProductReaderPort $products,
    ) {
    }

    public function __invoke(string $supplierInvoiceId): View|RedirectResponse
    {
        $result = $this->details->handle($supplierInvoiceId);

        if ($result->isFailure()) {
            return redirect()
                ->route('admin.procurement.supplier-invoices.index')
                ->with('error', $result->message() ?? 'Nota supplier tidak ditemukan.');
        }

        $payload = $result->data();
        if (! is_array($payload)) {
            return redirect()
                ->route('admin.procurement.supplier-invoices.index')
                ->with('error', 'Detail nota supplier tidak valid.');
        }

        $summary = is_array($payload['summary'] ?? null) ? $payload['summary'] : [];
        $lines = is_array($payload['lines'] ?? null) ? $payload['lines'] : [];

        $policyState = (string) ($summary['policy_state'] ?? 'locked');

        if ($policyState !== 'editable') {
            return redirect()
                ->route('admin.procurement.supplier-invoices.show', ['supplierInvoiceId' => $supplierInvoiceId])
                ->with('error', 'Nota supplier ini sudah terkunci. Gunakan correction / reversal.');
        }

        return view('admin.procurement.supplier_invoices.edit', [
            'summary' => $summary,
            'lines' => $lines,
            'formDefaults' => [
                'nomor_faktur' => old('nomor_faktur', (string) ($summary['nomor_faktur'] ?? '')),
                'nama_pt_pengirim' => old('nama_pt_pengirim', (string) ($summary['supplier_nama_pt_pengirim_snapshot'] ?? '')),
                'tanggal_pengiriman' => old('tanggal_pengiriman', (string) ($summary['shipment_date'] ?? '')),
            ],
            'lineItemsView' => $this->buildLineItemsView($this->products->findAll(), $lines),
        ]);
    }

    /**
     * @param array<int, Product> $products
     * @param list<array<string, mixed>> $existingLines
     * @return array<int, array<string, string|int>>
     */
    private function buildLineItemsView(array $products, array $existingLines): array
    {
        $productLabelsById = [];

        foreach ($products as $product) {
            $productLabelsById[$product->id()] = $this->buildProductLabel($product);
        }

        $oldLines = old('lines');

        if (! is_array($oldLines) || $oldLines === []) {
            $oldLines = array_map(
                static fn (array $line): array => [
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
