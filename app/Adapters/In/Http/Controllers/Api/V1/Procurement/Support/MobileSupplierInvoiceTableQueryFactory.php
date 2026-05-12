<?php

declare(strict_types=1);

namespace App\Adapters\In\Http\Controllers\Api\V1\Procurement\Support;

use App\Application\Procurement\DTO\ProcurementInvoiceTableQuery;
use Illuminate\Http\Request;

final class MobileSupplierInvoiceTableQueryFactory
{
    public function fromRequest(Request $request): ProcurementInvoiceTableQuery
    {
        return ProcurementInvoiceTableQuery::fromValidated([
            'q' => $this->nullableString($request, 'q'),
            'payment_status' => $this->oneOf($request, 'payment_status', ['active', 'outstanding', 'paid', 'all', 'voided'], 'all'),
            'page' => $this->positiveInt($request, 'page', 1),
            'per_page' => 10,
            'sort_by' => $this->sortBy($request),
            'sort_dir' => $this->oneOf($request, 'sort_dir', ['asc', 'desc'], 'desc'),
            'shipment_date_from' => $this->nullableString($request, 'shipment_date_from'),
            'shipment_date_to' => $this->nullableString($request, 'shipment_date_to'),
        ]);
    }

    private function nullableString(Request $request, string $key): ?string
    {
        $value = $request->query($key);

        if (! is_string($value)) {
            return null;
        }

        $normalized = trim($value);

        return $normalized === '' ? null : $normalized;
    }

    /** @param list<string> $allowed */
    private function oneOf(Request $request, string $key, array $allowed, string $default): string
    {
        $value = $this->nullableString($request, $key) ?? $default;

        return in_array($value, $allowed, true) ? $value : $default;
    }

    private function positiveInt(Request $request, string $key, int $default): int
    {
        $value = $request->query($key);

        return is_numeric($value) ? max(1, (int) $value) : $default;
    }

    private function sortBy(Request $request): string
    {
        return $this->oneOf($request, 'sort_by', [
            'shipment_date',
            'due_date',
            'nama_pt_pengirim',
            'grand_total_rupiah',
            'total_paid_rupiah',
            'outstanding_rupiah',
            'receipt_count',
            'total_received_qty',
        ], 'shipment_date');
    }
}
