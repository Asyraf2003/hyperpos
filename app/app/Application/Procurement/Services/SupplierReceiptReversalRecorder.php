<?php

declare(strict_types=1);

namespace App\Application\Procurement\Services;

use App\Ports\Out\AuditLogPort;
use App\Ports\Out\UuidPort;
use DateTimeImmutable;
use Illuminate\Support\Facades\DB;

final class SupplierReceiptReversalRecorder
{
    public function __construct(
        private readonly UuidPort $uuid,
        private readonly AuditLogPort $audit,
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

        DB::table('supplier_receipt_reversals')->insert([
            'id' => $reversalId,
            'supplier_receipt_id' => trim($supplierReceiptId),
            'reason' => trim($reason),
            'performed_by_actor_id' => trim($actorId),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->audit->record('supplier_receipt_reversed', [
            'reversal_id' => $reversalId,
            'supplier_receipt_id' => trim($supplierReceiptId),
            'supplier_invoice_id' => trim($supplierInvoiceId),
            'reason' => trim($reason),
            'reversed_at' => $reversedAt->format('Y-m-d'),
            'performed_by_actor_id' => trim($actorId),
            'delta_movement_count' => $deltaMovementCount,
        ]);

        return $reversalId;
    }
}
