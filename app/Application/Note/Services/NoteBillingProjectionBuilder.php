<?php

declare(strict_types=1);

namespace App\Application\Note\Services;

use App\Application\Payment\Services\ResolveNotePayableComponents;
use App\Core\Note\WorkItem\WorkItem;
use App\Core\Payment\PaymentComponentAllocation\PaymentComponentType;
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
    ) {
    }

    /**
     * @return list<array<string, mixed>>|null
     */
    public function build(string $noteId): ?array
    {
        $note = $this->notes->getById(trim($noteId));

        if ($note === null) {
            return null;
        }

        $workItems = [];

        foreach ($note->workItems() as $item) {
            $workItems[$item->id()] = $item;
        }

        $allocatedTotals = [];
        foreach ($this->payments->listByNoteId($note->id()) as $allocation) {
            $key = $this->componentKey($allocation->componentType(), $allocation->componentRefId());
            $allocatedTotals[$key] = ($allocatedTotals[$key] ?? 0) + $allocation->allocatedAmountRupiah()->amount();
        }

        $refundedTotals = [];
        foreach ($this->refunds->listByNoteId($note->id()) as $allocation) {
            $key = $this->componentKey($allocation->componentType(), $allocation->componentRefId());
            $refundedTotals[$key] = ($refundedTotals[$key] ?? 0) + $allocation->refundedAmountRupiah()->amount();
        }

        $rows = [];
        $remainingOutstandingByWorkItem = [];

        foreach ($this->components->fromNote($note) as $component) {
            $workItem = $workItems[$component->workItemId()] ?? null;

            if ($workItem === null) {
                continue;
            }

            $key = $this->componentKey($component->componentType(), $component->componentRefId());
            $componentTotal = $component->amountRupiah()->amount();
            $allocated = (int) ($allocatedTotals[$key] ?? 0);
            $refunded = (int) ($refundedTotals[$key] ?? 0);
            $netPaid = max($allocated - $refunded, 0);
            $outstanding = max($componentTotal - $netPaid, 0);

            $priorOutstanding = (int) ($remainingOutstandingByWorkItem[$component->workItemId()] ?? 0);
            $selectionBlocked = $priorOutstanding > 0 && $outstanding > 0;
            $remainingOutstandingByWorkItem[$component->workItemId()] = $priorOutstanding + $outstanding;

            $rows[] = [
                'id' => sprintf('%s::%s::%s', $component->workItemId(), $component->componentType(), $component->componentRefId()),
                'work_item_id' => $component->workItemId(),
                'line_no' => $workItem->lineNo(),
                'transaction_type' => $workItem->transactionType(),
                'domain_type_label' => $this->domainTypeLabel($workItem),
                'component_type' => $component->componentType(),
                'component_ref_id' => $component->componentRefId(),
                'component_label' => $this->componentLabel($component->componentType()),
                'component_group_label' => $this->componentGroupLabel($component->componentType()),
                'component_order' => $component->orderIndex(),
                'component_total_rupiah' => $componentTotal,
                'allocated_rupiah' => $allocated,
                'refunded_rupiah' => $refunded,
                'net_paid_rupiah' => $netPaid,
                'outstanding_rupiah' => $outstanding,
                'is_paid' => $outstanding <= 0,
                'is_outstanding' => $outstanding > 0,
                'is_product_component' => $this->isProductComponent($component->componentType()),
                'is_service_component' => $component->componentType() === PaymentComponentType::SERVICE_FEE,
                'eligible_for_dp_preset' => $this->isProductComponent($component->componentType()) && $outstanding > 0,
                'can_select_manually' => ! $selectionBlocked && $outstanding > 0,
                'selection_blocked_reason' => $selectionBlocked
                    ? 'Komponen sebelumnya pada line ini belum lunas. Ikuti urutan tagihan existing.'
                    : null,
                'status_label' => $outstanding <= 0 ? 'Lunas' : ($netPaid > 0 ? 'Parsial' : 'Belum Dibayar'),
            ];
        }

        return $rows;
    }

    private function componentKey(string $componentType, string $componentRefId): string
    {
        return trim($componentType) . '::' . trim($componentRefId);
    }

    private function isProductComponent(string $componentType): bool
    {
        return in_array($componentType, [
            PaymentComponentType::PRODUCT_ONLY_WORK_ITEM,
            PaymentComponentType::SERVICE_STORE_STOCK_PART,
            PaymentComponentType::SERVICE_EXTERNAL_PURCHASE_PART,
        ], true);
    }

    private function componentLabel(string $componentType): string
    {
        return match ($componentType) {
            PaymentComponentType::PRODUCT_ONLY_WORK_ITEM => 'Produk Toko',
            PaymentComponentType::SERVICE_STORE_STOCK_PART => 'Part Toko',
            PaymentComponentType::SERVICE_EXTERNAL_PURCHASE_PART => 'Part External',
            PaymentComponentType::SERVICE_FEE => 'Jasa',
            default => 'Komponen Tagihan',
        };
    }

    private function componentGroupLabel(string $componentType): string
    {
        return $this->isProductComponent($componentType) ? 'Produk' : 'Jasa';
    }

    private function domainTypeLabel(WorkItem $item): string
    {
        return match ($item->transactionType()) {
            WorkItem::TYPE_STORE_STOCK_SALE_ONLY => 'Produk Toko',
            WorkItem::TYPE_SERVICE_ONLY => 'Service Only',
            WorkItem::TYPE_SERVICE_WITH_STORE_STOCK_PART => 'Service + Part Toko',
            WorkItem::TYPE_SERVICE_WITH_EXTERNAL_PURCHASE => 'Service + Part External',
            default => 'Line Nota',
        };
    }
}
