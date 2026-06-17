<?php

declare(strict_types=1);

namespace Tests\Unit\Adapters\In\Http\Requests\Procurement;

use App\Adapters\In\Http\Requests\Procurement\CreateSupplierInvoiceRequest;
use App\Adapters\In\Http\Requests\Procurement\CreateSupplierInvoiceRequestText;
use App\Adapters\In\Http\Requests\Procurement\UpdateSupplierInvoiceRequest;
use PHPUnit\Framework\TestCase;

final class SupplierInvoiceLineTaxRequestRulesTest extends TestCase
{
    public function test_create_request_accepts_line_tax_input(): void
    {
        $rules = (new CreateSupplierInvoiceRequest())->rules();

        self::assertSame(['nullable', 'string', 'max:64'], $rules['lines.*.tax_input']);
    }

    public function test_update_request_accepts_line_tax_input(): void
    {
        $rules = (new UpdateSupplierInvoiceRequest())->rules();

        self::assertSame(['nullable', 'string', 'max:64'], $rules['lines.*.tax_input']);
    }

    public function test_create_request_text_contains_line_tax_labels(): void
    {
        $messages = CreateSupplierInvoiceRequestText::messages();
        $attributes = CreateSupplierInvoiceRequestText::attributes();

        self::assertSame('Pajak per rincian harus berupa teks.', $messages['lines.*.tax_input.string']);
        self::assertSame('Pajak per rincian maksimal 64 karakter.', $messages['lines.*.tax_input.max']);
        self::assertSame('pajak per rincian', $attributes['lines.*.tax_input']);
    }
}
