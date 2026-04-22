<?php

declare(strict_types=1);

namespace App\Application\Note\Services;

use App\Application\Payment\Services\ResolveNotePayableComponents;
use App\Ports\Out\Note\NoteReaderPort;
use App\Ports\Out\Payment\PaymentComponentAllocationReaderPort;
use App\Ports\Out\Payment\RefundComponentAllocationReaderPort;

final class NoteBillingProjectionBuilder
{
    public function __construct(
        private readonly NoteReaderPort $notes,
        private readonly ResolveNotePayableComponents $components,
        private readonly PaymentComponentAllocationReaderPort $payments,
        private readonly RefundComponentAllocationReaderPort $refunds,
        private readonly NoteBillingProjectionRowMapper $rows,
    ) {
    }

    public function build(string $noteId): ?array
    {
        $note = $this->notes->getById(trim($noteId));
        if ($note === null) return null;

        $items = [];
        foreach ($note->workItems() as $item) $items[$item->id()] = $item;

        $paid = $this->sumAllocated($note->id());
        $refunded = $this->sumRefunded($note->id());
        $rows = [];
        $lineOutstanding = [];

        foreach ($this->components->fromNote($note) as $component) {
            $item = $items[$component->workItemId()] ?? null;
            if ($item === null) continue;
            $rows[] = $this->rows->map($component, $item, $paid, $refunded, $lineOutstanding);
        }

        return $rows;
    }

    public function buildFromWorkspaceRows(array $rows): array
    {
        $billing = [];

        foreach ($rows as $row) {
            if (!is_array($row)) continue;

            $outstanding = (int) ($row['outstanding_rupiah'] ?? 0);
            $netPaid = (int) ($row['net_paid_rupiah'] ?? 0);
            $refunded = (int) ($row['refunded_rupiah'] ?? 0);

            $billing[] = [
                'id' => (string) ($row['id'] ?? ''),
                'work_item_id' => (string) ($row['id'] ?? ''),
                'line_no' => (int) ($row['line_no'] ?? 0),
                'transaction_type' => (string) ($row['transaction_type'] ?? ''),
                'domain_type_label' => (string) ($row['type_label'] ?? 'Line Nota'),
                'component_type' => 'line_total',
                'component_ref_id' => (string) ($row['id'] ?? ''),
                'component_label' => (string) ($row['line_label'] ?? 'Total Line'),
                'component_group_label' => 'Line Total',
                'component_order' => 1,
                'component_total_rupiah' => (int) ($row['subtotal_rupiah'] ?? 0),
                'allocated_rupiah' => (int) ($row['allocated_rupiah'] ?? 0),
                'refunded_rupiah' => $refunded,
                'net_paid_rupiah' => $netPaid,
                'outstanding_rupiah' => $outstanding,
                'is_paid' => $outstanding <= 0,
                'is_outstanding' => $outstanding > 0,
                'is_product_component' => (bool) ($row['store_stock_count'] ?? 0),
                'is_service_component' => !(bool) ($row['store_stock_count'] ?? 0),
                'eligible_for_dp_preset' => false,
                'can_select_manually' => $outstanding > 0,
                'selection_blocked_reason' => null,
                'status_label' => $outstanding <= 0 ? 'Lunas' : ($netPaid > 0 ? 'Parsial' : 'Belum Dibayar'),
            ];
        }

        return $billing;
    }

    private function sumAllocated(string $noteId): array
    {
        $totals = [];
        foreach ($this->payments->listByNoteId($noteId) as $allocation) {
            $key = $allocation->componentType() . '::' . $allocation->componentRefId();
            $totals[$key] = ($totals[$key] ?? 0) + $allocation->allocatedAmountRupiah()->amount();
        }

        return $totals;
    }

    private function sumRefunded(string $noteId): array
    {
        $totals = [];
        foreach ($this->refunds->listByNoteId($noteId) as $allocation) {
            $key = $allocation->componentType() . '::' . $allocation->componentRefId();
            $totals[$key] = ($totals[$key] ?? 0) + $allocation->refundedAmountRupiah()->amount();
        }

        return $totals;
    }
}
