<?php

declare(strict_types=1);

namespace Tests\Unit\Core\Note\WorkItem;

use App\Core\Note\WorkItem\ExternalPurchaseLine;
use App\Core\Shared\Exceptions\DomainException;
use App\Core\Shared\ValueObjects\Money;
use PHPUnit\Framework\TestCase;

final class ExternalPurchaseLineTest extends TestCase
{
    public function test_it_calculates_line_total_from_unit_cost_and_qty(): void
    {
        $line = ExternalPurchaseLine::create(
            'external-line-1',
            'Busi beli luar',
            Money::fromInt(15000),
            2,
        );

        $this->assertSame('external-line-1', $line->id());
        $this->assertSame('Busi beli luar', $line->costDescription());
        $this->assertSame(15000, $line->unitCostRupiah()->amount());
        $this->assertSame(2, $line->qty());
        $this->assertSame(30000, $line->lineTotalRupiah()->amount());
    }

    public function test_it_rejects_non_positive_qty(): void
    {
        $this->expectException(DomainException::class);

        ExternalPurchaseLine::create(
            'external-line-1',
            'Busi beli luar',
            Money::fromInt(15000),
            0,
        );
    }
}
