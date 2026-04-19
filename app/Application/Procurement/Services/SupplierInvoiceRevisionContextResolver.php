<?php

declare(strict_types=1);

namespace App\Application\Procurement\Services;

use App\Application\Procurement\DTO\SupplierInvoiceRevisionContextSnapshot;
use App\Application\Procurement\UseCases\GetProcurementInvoiceDetailHandler;
use App\Core\Procurement\SupplierInvoice\SupplierInvoice;
use DateTimeImmutable;

final class SupplierInvoiceRevisionContextResolver
{
    public function __construct(
        private readonly GetProcurementInvoiceDetailHandler $details,
    ) {
    }

    public function resolve(string $supplierInvoiceId, SupplierInvoice $updated): SupplierInvoiceRevisionContextSnapshot
    {
        $summary = $this->summary($supplierInvoiceId);

        return new SupplierInvoiceRevisionContextSnapshot(
            (int) ($summary['total_paid_rupiah'] ?? 0),
            (int) ($summary['total_received_qty'] ?? 0),
            $this->movementDate($updated, $summary),
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function summary(string $supplierInvoiceId): array
    {
        $detail = $this->details->handle($supplierInvoiceId);
        $payload = $detail->data();

        if (! is_array($payload)) {
            return [];
        }

        return is_array($payload['summary'] ?? null) ? $payload['summary'] : [];
    }

    /**
     * @param array<string, mixed> $summary
     */
    private function movementDate(SupplierInvoice $updated, array $summary): DateTimeImmutable
    {
        $latestReceiptDate = $summary['latest_receipt_date'] ?? null;

        if (is_string($latestReceiptDate) && trim($latestReceiptDate) !== '') {
            $parsed = DateTimeImmutable::createFromFormat('!Y-m-d', trim($latestReceiptDate));

            if ($parsed !== false) {
                return $parsed->modify('+1 day');
            }
        }

        return $updated->tanggalPengiriman();
    }
}
