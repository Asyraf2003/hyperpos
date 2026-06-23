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
        $serviceTotal = $this->requiredInt(
            $item['service']['price_rupiah'] ?? null,
            'Harga servis wajib diisi.'
        );

        $pricedLines = (new CreateTransactionWorkspaceServiceStoreStockPackageProductLinesComposer($this->products))
            ->compose($item['product_lines'] ?? []);

        if ($this->rules->requiresTemplate($item)) {
            return $this->composeWithTemplate($item, $pricedLines, $serviceTotal);
        }

        return $this->composeWithoutTemplate($item, $pricedLines, $serviceTotal);
    }
}
