<?php

declare(strict_types=1);

namespace App\Application\Note\Services;

use App\Ports\Out\ProductCatalog\ProductReaderPort;

final class CreateTransactionWorkspaceServiceStoreStockPackageAutoSplitComposer
{
    use CreateTransactionWorkspaceServiceStoreStockPackageAutoSplitPayload;
    use CreateTransactionWorkspaceServiceStoreStockPackageAutoSplitBranches;

    public function __construct(
        private readonly ProductReaderPort $products,
        private readonly CreateTransactionWorkspaceServiceStoreStockPackageTemplateRules $rules,
    ) {
    }

    /**
     * @param array<string, mixed> $item
     * @return array<string, mixed>
     */
    public function compose(array $item): array
    {
        $pricedLines = (new CreateTransactionWorkspaceServiceStoreStockPackageProductLinesComposer($this->products))
            ->compose($item['product_lines'] ?? []);
        $serviceTotal = $this->serviceTotal($item, $pricedLines['sparepart_total_rupiah']);

        if ($this->rules->requiresTemplate($item)) {
            return $this->composeWithTemplate($item, $pricedLines, $serviceTotal);
        }

        return $this->composeWithoutTemplate($item, $pricedLines, $serviceTotal);
    }

    /**
     * @param array<string, mixed> $item
     */
    private function serviceTotal(array $item, int $sparepartTotal): int
    {
        $serviceTotal = $item['service']['price_rupiah'] ?? null;

        if (is_int($serviceTotal) && $serviceTotal > 0) {
            return $serviceTotal;
        }

        $legacyPackageTotal = $item['package_total_rupiah'] ?? null;

        if (is_int($legacyPackageTotal) && $legacyPackageTotal > $sparepartTotal) {
            return $legacyPackageTotal - $sparepartTotal;
        }

        return $this->requiredInt($serviceTotal, 'Harga servis wajib diisi.');
    }
}
