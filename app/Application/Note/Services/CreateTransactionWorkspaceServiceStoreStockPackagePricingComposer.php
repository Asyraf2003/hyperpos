<?php

declare(strict_types=1);

namespace App\Application\Note\Services;

use App\Core\Shared\Exceptions\DomainException;
use App\Ports\Out\ProductCatalog\ProductReaderPort;
use App\Ports\Out\ServiceProductTemplate\ServiceProductTemplateLookupReaderPort;

final class CreateTransactionWorkspaceServiceStoreStockPackagePricingComposer
{
    public function __construct(
        private readonly ProductReaderPort $products,
        private readonly ServiceProductTemplateLookupReaderPort $templates,
    ) {
    }

    /** @param array<string, mixed> $item @return array<string, mixed> */
    public function compose(array $item): array
    {
        $rules = new CreateTransactionWorkspaceServiceStoreStockPackageTemplateRules($this->templates);

        if (! $rules->hasProductLine($item['product_lines'] ?? [])) {
            return $item;
        }

        if (($item['pricing_mode'] ?? null) !== 'package_auto_split') {
            $this->assertManualPricingAllowed($rules, $item);

            return $item;
        }

        return (new CreateTransactionWorkspaceServiceStoreStockPackageAutoSplitComposer($this->products, $rules))
            ->compose($item);
    }

    /** @param array<string, mixed> $item */
    private function assertManualPricingAllowed(
        CreateTransactionWorkspaceServiceStoreStockPackageTemplateRules $rules,
        array $item
    ): void {
        if ($rules->requiresTemplate($item)) {
            throw new DomainException('Paket servis + produk wajib memakai template aktif.');
        }
    }
}
