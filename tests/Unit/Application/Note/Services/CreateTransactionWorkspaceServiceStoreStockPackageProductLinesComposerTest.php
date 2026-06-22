<?php

declare(strict_types=1);

namespace Tests\Unit\Application\Note\Services;

use App\Application\Note\Services\CreateTransactionWorkspaceServiceStoreStockPackageProductLinesComposer;
use App\Core\ProductCatalog\Product\Product;
use App\Core\Shared\ValueObjects\Money;
use App\Ports\Out\ProductCatalog\ProductReaderPort;
use PHPUnit\Framework\TestCase;

final class CreateTransactionWorkspaceServiceStoreStockPackageProductLinesComposerTest extends TestCase
{
    public function test_it_preserves_trusted_revision_snapshot_unit_prices_for_all_product_lines(): void
    {
        $composer = new CreateTransactionWorkspaceServiceStoreStockPackageProductLinesComposer(
            $this->products([
                'product-1' => 999999,
                'product-2' => 888888,
            ])
        );

        $result = $composer->compose([
            [
                'product_id' => 'product-1',
                'qty' => 2,
                'unit_price_rupiah' => 50000,
                'price_basis' => 'revision_snapshot',
                '_server_trusted_revision_snapshot' => true,
            ],
            [
                'product_id' => 'product-2',
                'qty' => 1,
                'unit_price_rupiah' => 30000,
                'price_basis' => 'revision_snapshot',
                '_server_trusted_revision_snapshot' => true,
            ],
        ]);

        $this->assertSame(130000, $result['sparepart_total_rupiah']);
        $this->assertSame(50000, $result['product_lines'][0]['unit_price_rupiah']);
        $this->assertSame(30000, $result['product_lines'][1]['unit_price_rupiah']);
    }

    public function test_it_uses_current_catalog_price_when_revision_snapshot_is_not_server_trusted(): void
    {
        $composer = new CreateTransactionWorkspaceServiceStoreStockPackageProductLinesComposer(
            $this->products([
                'product-1' => 999999,
            ])
        );

        $result = $composer->compose([
            [
                'product_id' => 'product-1',
                'qty' => 2,
                'unit_price_rupiah' => 50000,
                'price_basis' => 'revision_snapshot',
                '_server_trusted_revision_snapshot' => false,
            ],
        ]);

        $this->assertSame(1999998, $result['sparepart_total_rupiah']);
        $this->assertSame(999999, $result['product_lines'][0]['unit_price_rupiah']);
    }

    /**
     * @param array<string, int> $prices
     */
    private function products(array $prices): ProductReaderPort
    {
        return new class($prices) implements ProductReaderPort {
            /**
             * @param array<string, int> $prices
             */
            public function __construct(private readonly array $prices)
            {
            }

            public function getById(string $productId): ?Product
            {
                if (! array_key_exists($productId, $this->prices)) {
                    return null;
                }

                return Product::rehydrate(
                    $productId,
                    strtoupper($productId),
                    'Produk '.$productId,
                    'Unit Test',
                    null,
                    Money::fromInt($this->prices[$productId]),
                    1,
                    1,
                );
            }

            public function findAll(): array
            {
                return [];
            }

            public function search(string $query): array
            {
                return [];
            }
        };
    }
}
