<?php

declare(strict_types=1);

namespace App\Application\Procurement\Services;

use App\Ports\Out\AuditLogPort;
use App\Ports\Out\Procurement\SupplierReceiptReversalWriterPort;
use App\Ports\Out\UuidPort;
use DateTimeImmutable;

final class SupplierReceiptReversalRecorder
{
    public function __construct(
        private readonly UuidPort $uuid,
        private readonly AuditLogPort $audit,
        private readonly SupplierReceiptReversalWriterPort $reversals,
    ) {
    }

    public function record(
        string $supplierReceiptId,
        string $supplierInvoiceId,
        string $reason,
        string $actorId,
        DateTimeImmutable $reversedAt,
        int $deltaMovementCount,
    ): string {
        $reversalId = $this->uuid->generate();
        $supplierReceiptId = trim($supplierReceiptId);
        $supplierInvoiceId = trim($supplierInvoiceId);
        $reason = trim($reason);
        $actorId = trim($actorId);

        $this->reversals->record([
            'id' => $reversalId,
            'supplier_receipt_id' => $supplierReceiptId,
            'reason' => $reason,
            'performed_by_actor_id' => $actorId,
        ]);

        $this->audit->record('supplier_receipt_reversed', [
            'reversal_id' => $reversalId,
            'supplier_receipt_id' => $supplierReceiptId,
            'supplier_invoice_id' => $supplierInvoiceId,
            'reason' => $reason,
            'reversed_at' => $reversedAt->format('Y-m-d'),
            'performed_by_actor_id' => $actorId,
            'delta_movement_count' => $deltaMovementCount,
        ]);

        return $reversalId;
    }
}
