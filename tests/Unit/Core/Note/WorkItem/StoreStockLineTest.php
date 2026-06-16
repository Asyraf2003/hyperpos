<?php

declare(strict_types=1);

namespace Tests\Unit\Core\Note\WorkItem;

use App\Core\Note\WorkItem\StoreStockLine;
use App\Core\Shared\Exceptions\DomainException;
use App\Core\Shared\ValueObjects\Money;
use PHPUnit\Framework\TestCase;

final class StoreStockLineTest extends TestCase
{
    public function test_it_keeps_product_qty_and_line_total_exactly(): void
    {
        $line = StoreStockLine::create(
            'store-line-1',
            'product-1',
            2,
            Money::fromInt(40000),
        );

        $this->assertSame('store-line-1', $line->id());
        $this->assertSame('product-1', $line->productId());
        $this->assertSame(2, $line->qty());
        $this->assertSame(40000, $line->lineTotalRupiah()->amount());
    }


    public function test_it_defaults_base_total_to_line_total_and_no_tax(): void
    {
        $line = StoreStockLine::create(
            'store-line-1',
            'product-1',
            2,
            Money::fromInt(40000),
        );

        $this->assertSame(40000, $line->baseTotalRupiah()->amount());
        $this->assertNull($line->taxInput());
        $this->assertSame(StoreStockLine::TAX_MODE_NONE, $line->taxMode());
        $this->assertNull($line->taxRateBasisPoints());
        $this->assertSame(0, $line->taxAmountRupiah()->amount());
    }

    public function test_it_keeps_tax_breakdown_when_provided(): void
    {
        $line = StoreStockLine::create(
            'store-line-tax-1',
            'product-1',
            2,
            Money::fromInt(44400),
            Money::fromInt(40000),
            '11%',
            StoreStockLine::TAX_MODE_PERCENT,
            1100,
            Money::fromInt(4400),
        );

        $this->assertSame(40000, $line->baseTotalRupiah()->amount());
        $this->assertSame('11%', $line->taxInput());
        $this->assertSame(StoreStockLine::TAX_MODE_PERCENT, $line->taxMode());
        $this->assertSame(1100, $line->taxRateBasisPoints());
        $this->assertSame(4400, $line->taxAmountRupiah()->amount());
        $this->assertSame(44400, $line->lineTotalRupiah()->amount());
    }

    public function test_it_rejects_non_positive_line_total(): void
    {
        $this->expectException(DomainException::class);

        StoreStockLine::create(
            'store-line-1',
            'product-1',
            2,
            Money::zero(),
        );
    }
}
