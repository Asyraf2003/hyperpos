<?php

declare(strict_types=1);

namespace App\Application\Procurement\DTO;

final readonly class SupplierPayableReminderRow
{
    public function __construct(
        public string $supplierInvoiceId,
        public string $nomorFaktur,
        public string $supplierId,
        public string $supplierName,
        public string $shipmentDate,
        public string $dueDate,
        public int $grandTotalRupiah,
        public int $totalPaidRupiah,
        public int $outstandingRupiah,
        public int $daysOverdue,
    ) {
    }
}
