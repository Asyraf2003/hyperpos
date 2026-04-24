<?php

declare(strict_types=1);

namespace Tests\Unit\Application\Note\Services;

use App\Application\Note\Services\RefundImpactPayloadBuilder;
use App\Application\Note\Services\RefundImpactProductLabelResolver;
use App\Core\Note\WorkItem\ExternalPurchaseLine;
use App\Core\Note\WorkItem\StoreStockLine;
use App\Core\ProductCatalog\Product\Product;
use App\Core\Shared\ValueObjects\Money;
use App\Ports\Out\ProductCatalog\ProductReaderPort;
use Illuminate\Pagination\LengthAwarePaginator;
use PHPUnit\Framework\TestCase;

final class RefundImpactPayloadBuilderTest extends TestCase
{
    public function test_it_builds_refund_impact_from_scalar_snapshot_payload(): void
    {
        $builder = new RefundImpactPayloadBuilder(
            new RefundImpactProductLabelResolver($this->products())
        );

        $impact = $builder->fromRevisionPayload([
            'store_stock_lines' => [[
                'id' => 'ssl-1',
                'product_id' => 'product-1',
                'qty' => 2,
                'line_total_rupiah' => 30000,
            ]],
            'external_purchase_lines' => [[
                'id' => 'ext-1',
                'cost_description' => 'Beli luar',
                'qty' => 1,
                'line_total_rupiah' => 2000,
            ]],
        ], 32000);

        $this->assertSame(32000, $impact['refund_amount_rupiah']);
        $this->assertSame(2, $impact['effect_summary']['stock_store_return_count']);
        $this->assertSame(1, $impact['effect_summary']['external_item_count']);
        $this->assertSame('Produk A — Merek A (PRD-1)', $impact['store_returns'][0]['product_label']);
        $this->assertSame(2000, $impact['external_returns'][0]['amount_rupiah']);
    }

    public function test_it_builds_refund_impact_from_object_payload_lines(): void
    {
        $builder = new RefundImpactPayloadBuilder(
            new RefundImpactProductLabelResolver($this->products())
        );

        $impact = $builder->fromRevisionPayload([
            'store_stock_lines' => [
                StoreStockLine::rehydrate('ssl-1', 'product-1', 1, Money::fromInt(15000)),
            ],
            'external_purchase_lines' => [
                ExternalPurchaseLine::rehydrate('ext-1', 'Beli luar', Money::fromInt(2000), 2),
            ],
        ], 19000);

        $this->assertSame(19000, $impact['refund_amount_rupiah']);
        $this->assertSame('ssl-1', $impact['store_returns'][0]['source_line_id']);
        $this->assertSame('product-1', $impact['store_returns'][0]['product_id']);
        $this->assertSame(1, $impact['store_returns'][0]['qty']);
        $this->assertSame('ext-1', $impact['external_returns'][0]['source_line_id']);
        $this->assertSame(2, $impact['external_returns'][0]['qty']);
        $this->assertSame(4000, $impact['external_returns'][0]['amount_rupiah']);
    }

    private function products(): ProductReaderPort
    {
        return new class () implements ProductReaderPort {
            public function getById(string $productId): ?Product
            {
                if ($productId !== 'product-1') {
                    return null;
                }

                return Product::rehydrate(
                    'product-1',
                    'PRD-1',
                    'Produk A',
                    'Merek A',
                    null,
                    Money::fromInt(25000),
                    null,
                    null,
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

            public function findPaginated(int $perPage = 10): LengthAwarePaginator
            {
                return new LengthAwarePaginator([], 0, $perPage);
            }

            public function searchPaginated(string $query, int $perPage = 10): LengthAwarePaginator
            {
                return new LengthAwarePaginator([], 0, $perPage);
            }
        };
    }
}
