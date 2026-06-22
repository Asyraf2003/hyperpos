<?php

declare(strict_types=1);

namespace Tests\Unit\Application\Procurement\Services;

use App\Application\Procurement\Services\SupplierInvoiceTaxInputCalculator;
use App\Application\Procurement\Services\SupplierInvoiceTaxLandedCostAllocator;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class SupplierInvoiceTaxRoundingResidueAllocationTest extends TestCase
{
    public function test_fractional_unit_cost_requires_explicit_rounding_residue_confirmation(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'Total setelah pajak tidak habis dibagi qty, sehingga modal per pcs akan dibulatkan dan selisih pembulatan akan dicatat. Lanjutkan?'
        );

        $this->allocator()->allocate([
            [
                'line_no' => 1,
                'product_id' => 'product-1',
                'qty_pcs' => 3,
                'line_total_rupiah' => 300,
            ],
        ], '1');
    }

    public function test_confirmed_fractional_unit_cost_records_rounding_residue(): void
    {
        $allocation = $this->allocator()->allocate([
            [
                'line_no' => 1,
                'product_id' => 'product-1',
                'qty_pcs' => 3,
                'line_total_rupiah' => 300,
            ],
        ], '1', roundingResidueConfirmed: true);

        $line = $allocation->lines()[0];

        self::assertSame(301, $allocation->grandTotalAfterTaxRupiah());
        self::assertSame(301, $line['line_total_rupiah']);
        self::assertSame(100, $line['unit_cost_rupiah']);
        self::assertSame(1, $line['rounding_residue_rupiah']);
    }

    private function allocator(): SupplierInvoiceTaxLandedCostAllocator
    {
        return new SupplierInvoiceTaxLandedCostAllocator(new SupplierInvoiceTaxInputCalculator());
    }
}
