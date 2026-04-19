<?php

declare(strict_types=1);

namespace App\Adapters\In\Http\Controllers\Admin\Procurement\Concerns;

trait FormatsProcurementInvoiceDetailViewValue
{
    private function formatRupiah(int $value): string
    {
        return 'Rp ' . number_format($value, 0, ',', '.');
    }
}
