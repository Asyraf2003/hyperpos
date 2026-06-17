<?php

declare(strict_types=1);

namespace App\Core\Procurement\SupplierInvoice;

use App\Core\Shared\Exceptions\DomainException;

final class SupplierInvoiceTaxSummaryValidation
{
    public static function assertSubtotalBeforeTax(int $subtotalBeforeTaxRupiah): void
    {
        if ($subtotalBeforeTaxRupiah < 0) {
            throw new DomainException('Subtotal sebelum pajak supplier invoice tidak boleh negatif.');
        }
    }

    public static function assertValid(SupplierInvoiceTaxSummary $summary): void
    {
        self::assertSubtotalBeforeTax($summary->subtotalBeforeTaxRupiah()->amount());

        if ($summary->taxAmountRupiah()->amount() < 0) {
            throw new DomainException('Nominal pajak supplier invoice tidak boleh negatif.');
        }

        if (! in_array($summary->taxMode(), self::validModes(), true)) {
            throw new DomainException('Mode pajak supplier invoice tidak valid.');
        }

        if ($summary->taxMode() === SupplierInvoiceTaxSummary::MODE_NONE) {
            self::assertNoneMode($summary);

            return;
        }

        self::assertTaxedMode($summary);
    }

    /** @return list<string> */
    private static function validModes(): array
    {
        return [
            SupplierInvoiceTaxSummary::MODE_NONE,
            SupplierInvoiceTaxSummary::MODE_PERCENT,
            SupplierInvoiceTaxSummary::MODE_FIXED,
        ];
    }

    private static function assertNoneMode(SupplierInvoiceTaxSummary $summary): void
    {
        if (
            $summary->taxInput() !== null
            || $summary->taxRateBasisPoints() !== null
            || $summary->taxAmountRupiah()->amount() !== 0
        ) {
            throw new DomainException('Pajak supplier invoice mode none harus kosong.');
        }
    }

    private static function assertTaxedMode(SupplierInvoiceTaxSummary $summary): void
    {
        if ($summary->taxInput() === null) {
            throw new DomainException('Input pajak supplier invoice wajib ada.');
        }

        if (
            $summary->taxMode() === SupplierInvoiceTaxSummary::MODE_PERCENT
            && ($summary->taxRateBasisPoints() === null || $summary->taxRateBasisPoints() < 0)
        ) {
            throw new DomainException('Basis points pajak supplier invoice tidak valid.');
        }

        if (
            $summary->taxMode() === SupplierInvoiceTaxSummary::MODE_FIXED
            && $summary->taxRateBasisPoints() !== null
        ) {
            throw new DomainException('Pajak fixed supplier invoice tidak boleh punya basis points.');
        }
    }
}
