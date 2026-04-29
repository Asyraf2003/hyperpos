<?php

declare(strict_types=1);

namespace Tests\Unit\Application\Reporting\Services;

use App\Application\Reporting\Services\CashChangeDenominationCalculator;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class CashChangeDenominationCalculatorTest extends TestCase
{
    public function test_it_returns_empty_rows_for_zero_change(): void
    {
        $calculator = new CashChangeDenominationCalculator();

        $this->assertSame([], $calculator->calculate(0));
    }

    public function test_it_breaks_down_37000_rupiah_change(): void
    {
        $calculator = new CashChangeDenominationCalculator();

        $this->assertSame(
            [
                ['denomination' => 20000, 'count' => 1, 'total_rupiah' => 20000],
                ['denomination' => 10000, 'count' => 1, 'total_rupiah' => 10000],
                ['denomination' => 5000, 'count' => 1, 'total_rupiah' => 5000],
                ['denomination' => 2000, 'count' => 1, 'total_rupiah' => 2000],
            ],
            $calculator->calculate(37000),
        );
    }

    public function test_it_uses_500_as_the_smallest_supported_denomination(): void
    {
        $calculator = new CashChangeDenominationCalculator();

        $this->assertSame(
            [
                ['denomination' => 1000, 'count' => 1, 'total_rupiah' => 1000],
                ['denomination' => 500, 'count' => 1, 'total_rupiah' => 500],
            ],
            $calculator->calculate(1500),
        );
    }

    public function test_it_aggregates_multiple_change_amounts(): void
    {
        $calculator = new CashChangeDenominationCalculator();

        $this->assertSame(
            [
                ['denomination' => 20000, 'count' => 2, 'total_rupiah' => 40000],
                ['denomination' => 10000, 'count' => 1, 'total_rupiah' => 10000],
                ['denomination' => 5000, 'count' => 1, 'total_rupiah' => 5000],
                ['denomination' => 2000, 'count' => 1, 'total_rupiah' => 2000],
                ['denomination' => 500, 'count' => 1, 'total_rupiah' => 500],
            ],
            $calculator->aggregate([37000, 20000, 500]),
        );
    }

    public function test_it_rejects_negative_change_amount(): void
    {
        $calculator = new CashChangeDenominationCalculator();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Change amount must not be negative.');

        $calculator->calculate(-500);
    }

    public function test_it_rejects_change_amount_that_cannot_be_represented(): void
    {
        $calculator = new CashChangeDenominationCalculator();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'Change amount cannot be represented by configured cash denominations.'
        );

        $calculator->calculate(499);
    }
}
