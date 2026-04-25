<?php

declare(strict_types=1);

namespace App\Application\Reporting\DTO;

final class SupplierPayableSummaryRow
{
    public function __construct(
        private readonly string $supplierInvoiceId,
        private readonly string $nomorFaktur,
        private readonly string $supplierId,
        private readonly string $supplierName,
        private readonly string $shipmentDate,
        private readonly string $dueDate,
        private readonly int $grandTotalRupiah,
        private readonly int $totalPaidRupiah,
        private readonly int $outstandingRupiah,
        private readonly int $receiptCount,
        private readonly int $totalReceivedQty,
        private readonly string $dueStatus,
        private readonly string $dueStatusLabel,
    ) {
    }

    public function supplierInvoiceId(): string { return $this->supplierInvoiceId; }
    public function nomorFaktur(): string { return $this->nomorFaktur; }
    public function supplierId(): string { return $this->supplierId; }
    public function supplierName(): string { return $this->supplierName; }
    public function shipmentDate(): string { return $this->shipmentDate; }
    public function dueDate(): string { return $this->dueDate; }
    public function grandTotalRupiah(): int { return $this->grandTotalRupiah; }
    public function totalPaidRupiah(): int { return $this->totalPaidRupiah; }
    public function outstandingRupiah(): int { return $this->outstandingRupiah; }
    public function receiptCount(): int { return $this->receiptCount; }
    public function totalReceivedQty(): int { return $this->totalReceivedQty; }
    public function dueStatus(): string { return $this->dueStatus; }
    public function dueStatusLabel(): string { return $this->dueStatusLabel; }

    public function toArray(): array
    {
        return [
            'supplier_invoice_id' => $this->supplierInvoiceId(),
            'nomor_faktur' => $this->nomorFaktur(),
            'supplier_id' => $this->supplierId(),
            'supplier_name' => $this->supplierName(),
            'shipment_date' => $this->shipmentDate(),
            'due_date' => $this->dueDate(),
            'grand_total_rupiah' => $this->grandTotalRupiah(),
            'total_paid_rupiah' => $this->totalPaidRupiah(),
            'outstanding_rupiah' => $this->outstandingRupiah(),
            'receipt_count' => $this->receiptCount(),
            'total_received_qty' => $this->totalReceivedQty(),
            'due_status' => $this->dueStatus(),
            'due_status_label' => $this->dueStatusLabel(),
        ];
    }
}
