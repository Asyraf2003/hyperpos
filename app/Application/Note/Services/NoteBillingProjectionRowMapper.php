<?php

declare(strict_types=1);

namespace App\Application\Note\Services;

use App\Application\Payment\DTO\PayableNoteComponent;
use App\Core\Note\WorkItem\WorkItem;

final class NoteBillingProjectionRowMapper
{
    public function __construct(
        private readonly NoteBillingProjectionSupport $support,
    ) {
    }

    public function map(
        PayableNoteComponent $component,
        WorkItem $item,
        array $paid,
        array $refunded,
        array &$lineOutstanding,
    ): array {
        $key = $this->support->componentKey($component->componentType(), $component->componentRefId());
        $total = $component->amountRupiah()->amount();
        $netPaid = max((int) ($paid[$key] ?? 0) - (int) ($refunded[$key] ?? 0), 0);
        $outstanding = max($total - $netPaid, 0);
        $priorOutstanding = (int) ($lineOutstanding[$component->workItemId()] ?? 0);
        $lineOutstanding[$component->workItemId()] = $priorOutstanding + $outstanding;
        $blocked = $priorOutstanding > 0 && $outstanding > 0;
        $isProduct = $this->support->isProductComponent($component->componentType());

        return [
            'id' => sprintf('%s::%s::%s', $component->workItemId(), $component->componentType(), $component->componentRefId()),
            'work_item_id' => $component->workItemId(),
            'line_no' => $item->lineNo(),
            'transaction_type' => $item->transactionType(),
            'domain_type_label' => $this->support->domainTypeLabel($item),
            'component_type' => $component->componentType(),
            'component_ref_id' => $component->componentRefId(),
            'component_label' => $this->support->componentLabel($component->componentType()),
            'component_group_label' => $this->support->componentGroupLabel($component->componentType()),
            'component_order' => $component->orderIndex(),
            'component_total_rupiah' => $total,
            'allocated_rupiah' => (int) ($paid[$key] ?? 0),
            'refunded_rupiah' => (int) ($refunded[$key] ?? 0),
            'net_paid_rupiah' => $netPaid,
            'outstanding_rupiah' => $outstanding,
            'is_paid' => $outstanding <= 0,
            'is_outstanding' => $outstanding > 0,
            'is_product_component' => $isProduct,
            'is_service_component' => ! $isProduct,
            'eligible_for_dp_preset' => $isProduct && $outstanding > 0,
            'can_select_manually' => ! $blocked && $outstanding > 0,
            'selection_blocked_reason' => $blocked ? 'Komponen sebelumnya pada line ini belum lunas. Ikuti urutan tagihan existing.' : null,
            'status_label' => $outstanding <= 0 ? 'Lunas' : ($netPaid > 0 ? 'Parsial' : 'Belum Dibayar'),
        ];
    }
}
