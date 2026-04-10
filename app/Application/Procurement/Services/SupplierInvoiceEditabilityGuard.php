<?php

declare(strict_types=1);

namespace App\Application\Procurement\Services;

use App\Application\Procurement\UseCases\GetProcurementInvoiceDetailHandler;
use App\Application\Shared\DTO\Result;

final class SupplierInvoiceEditabilityGuard
{
    public function __construct(
        private readonly GetProcurementInvoiceDetailHandler $details,
    ) {
    }

    public function ensureEditable(string $supplierInvoiceId): Result
    {
        $detail = $this->details->handle($supplierInvoiceId);
        $payload = $detail->data();
        $summary = is_array($payload) && is_array($payload['summary'] ?? null)
            ? $payload['summary']
            : [];

        if ((string) ($summary['policy_state'] ?? 'locked') !== 'editable') {
            return Result::failure(
                'Nota supplier ini sudah terkunci. Gunakan correction / reversal.',
                ['supplier_invoice' => ['SUPPLIER_INVOICE_LOCKED']]
            );
        }

        return Result::success();
    }
}
