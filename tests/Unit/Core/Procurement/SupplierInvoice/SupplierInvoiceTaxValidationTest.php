<?php

declare(strict_types=1);

namespace Tests\Unit\Core\Procurement\SupplierInvoice;

use App\Core\Procurement\SupplierInvoice\SupplierInvoice;
use App\Core\Procurement\SupplierInvoice\SupplierInvoiceLine;
use App\Core\Procurement\SupplierInvoice\SupplierInvoiceTaxSummary;
use App\Core\Shared\Exceptions\DomainException;
use App\Core\Shared\ValueObjects\Money;
use DateTimeImmutable;
use Tests\TestCase;

final class SupplierInvoiceTaxValidationTest extends TestCase
{
    public function test_invoice_accepts_line_tax_only_when_summary_has_no_header_tax(): void
    {
        $line = SupplierInvoiceLine::create(
            'line-tax-only-1',
            1,
            'product-tax-only-1',
            'KB-TAX-001',
            'Ban Tax',
            'Federal',
            100,
            1,
            Money::fromInt(111000),
            Money::fromInt(100000),
            '11%',
            SupplierInvoiceTaxSummary::MODE_PERCENT,
            1100,
            Money::fromInt(11000),
        );

        $invoice = SupplierInvoice::create(
            'invoice-tax-only-1',
            'supplier-tax-only-1',
            'PT Supplier Tax',
            'INV-TAX-001',
            new DateTimeImmutable('2026-06-19'),
            [$line],
            SupplierInvoiceTaxSummary::none(100000),
        );

        self::assertSame(111000, $invoice->grandTotalRupiah()->amount());
        self::assertSame(100000, $invoice->subtotalBeforeTaxRupiah()->amount());
        self::assertSame(0, $invoice->taxAmountRupiah()->amount());
    }

    public function test_invoice_accepts_header_tax_landed_to_line_total(): void
    {
        $line = SupplierInvoiceLine::create(
            'line-header-tax-1',
            1,
            'product-header-tax-1',
            'KB-TAX-002',
            'Ban Header Tax',
            'Federal',
            100,
            1,
            Money::fromInt(111000),
            Money::fromInt(111000),
            null,
            SupplierInvoiceTaxSummary::MODE_NONE,
            null,
            Money::fromInt(0),
        );

        $invoice = SupplierInvoice::create(
            'invoice-header-tax-1',
            'supplier-header-tax-1',
            'PT Supplier Header Tax',
            'INV-TAX-002',
            new DateTimeImmutable('2026-06-19'),
            [$line],
            SupplierInvoiceTaxSummary::rehydrate(
                100000,
                '11%',
                SupplierInvoiceTaxSummary::MODE_PERCENT,
                1100,
                11000,
            ),
        );

        self::assertSame(111000, $invoice->grandTotalRupiah()->amount());
        self::assertSame(100000, $invoice->subtotalBeforeTaxRupiah()->amount());
        self::assertSame(11000, $invoice->taxAmountRupiah()->amount());
    }

    public function test_invoice_accepts_line_tax_plus_header_tax_landed_to_lines(): void
    {
        $lineOne = SupplierInvoiceLine::create(
            'line-tax-mixed-1',
            1,
            'product-tax-mixed-1',
            'KB-TAX-003',
            'Ban Mixed Tax',
            'Federal',
            100,
            1,
            Money::fromInt(122100),
            Money::fromInt(100000),
            '11%',
            SupplierInvoiceTaxSummary::MODE_PERCENT,
            1100,
            Money::fromInt(11000),
        );

        $lineTwo = SupplierInvoiceLine::create(
            'line-tax-mixed-2',
            2,
            'product-tax-mixed-2',
            'KB-TAX-004',
            'Oli Mixed Tax',
            'Federal',
            100,
            1,
            Money::fromInt(60500),
            Money::fromInt(50000),
            '5000',
            SupplierInvoiceTaxSummary::MODE_FIXED,
            null,
            Money::fromInt(5000),
        );

        $invoice = SupplierInvoice::create(
            'invoice-tax-mixed-1',
            'supplier-tax-mixed-1',
            'PT Supplier Mixed Tax',
            'INV-TAX-003',
            new DateTimeImmutable('2026-06-19'),
            [$lineOne, $lineTwo],
            SupplierInvoiceTaxSummary::rehydrate(
                150000,
                '10%',
                SupplierInvoiceTaxSummary::MODE_PERCENT,
                1000,
                16600,
            ),
        );

        self::assertSame(182600, $invoice->grandTotalRupiah()->amount());
        self::assertSame(150000, $invoice->subtotalBeforeTaxRupiah()->amount());
        self::assertSame(16600, $invoice->taxAmountRupiah()->amount());
    }

    public function test_invoice_rejects_tax_summary_when_landed_grand_total_does_not_match(): void
    {
        $line = SupplierInvoiceLine::create(
            'line-tax-mismatch-1',
            1,
            'product-tax-mismatch-1',
            'KB-TAX-005',
            'Ban Tax Mismatch',
            'Federal',
            100,
            1,
            Money::fromInt(111000),
            Money::fromInt(100000),
            '11%',
            SupplierInvoiceTaxSummary::MODE_PERCENT,
            1100,
            Money::fromInt(11000),
        );

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Grand total supplier invoice tidak cocok dengan subtotal dan pajak.');

        SupplierInvoice::create(
            'invoice-tax-mismatch-1',
            'supplier-tax-mismatch-1',
            'PT Supplier Tax Mismatch',
            'INV-TAX-004',
            new DateTimeImmutable('2026-06-19'),
            [$line],
            SupplierInvoiceTaxSummary::rehydrate(
                90000,
                null,
                SupplierInvoiceTaxSummary::MODE_NONE,
                null,
                0,
            ),
        );
    }
}
