<?php

declare(strict_types=1);

namespace App\Application\Note\Services\CurrentRevision;

use App\Application\Note\Services\NoteBillingProjectionRefundedStoreStockComponentSkipper;
use App\Core\Note\Revision\NoteRevisionLineSnapshot;
use App\Core\Payment\PaymentComponentAllocation\PaymentComponentType;

final class CurrentRevisionComponentCollectibleSettlementBuilder
{
    public function __construct(
        private readonly CurrentRevisionLineBillingComponentMapper $components,
        private readonly NoteBillingProjectionRefundedStoreStockComponentSkipper $refundedStoreStock,
    ) {
    }

    /**
     * @param array<string, int> $componentPaymentTotals
     * @param array<string, int> $componentRefundTotals
     * @return array{net_paid_rupiah:int,outstanding_rupiah:int}
     */
    public function build(
        NoteRevisionLineSnapshot $line,
        array $componentPaymentTotals,
        array $componentRefundTotals,
    ): array {
        $netPaid = 0;
        $outstanding = 0;

        foreach ($this->components->map($line, $line->payload()) as $component) {
            $settlement = $this->settleComponent(
                $component,
                $componentPaymentTotals,
                $componentRefundTotals,
            );

            $netPaid += $settlement['net_paid_rupiah'];
            $outstanding += $settlement['outstanding_rupiah'];
        }

        return [
            'net_paid_rupiah' => $netPaid,
            'outstanding_rupiah' => $outstanding,
        ];
    }

    /** @return array{net_paid_rupiah:int,outstanding_rupiah:int} */
    private function settleComponent(array $component, array $payments, array $refunds): array
    {
        $type = (string) ($component['component_type'] ?? '');
        $refId = (string) ($component['component_ref_id'] ?? '');
        $total = (int) ($component['component_total_rupiah'] ?? 0);

        if ($type === '' || $refId === '' || $total <= 0) {
            return ['net_paid_rupiah' => 0, 'outstanding_rupiah' => 0];
        }

        $key = $type . '::' . $refId;
        $allocated = min((int) ($payments[$key] ?? 0), $total);
        $refunded = min((int) ($refunds[$key] ?? 0), $allocated);

        if ($this->isNonCollectibleRefundedProduct($type, $refId, $allocated, $refunded)) {
            return ['net_paid_rupiah' => 0, 'outstanding_rupiah' => 0];
        }

        $netPaid = max($allocated - $refunded, 0);

        return [
            'net_paid_rupiah' => $netPaid,
            'outstanding_rupiah' => max($total - $netPaid, 0),
        ];
    }

    private function isNonCollectibleRefundedProduct(
        string $type,
        string $refId,
        int $allocated,
        int $refunded,
    ): bool {
        if ($refunded <= 0 || $allocated <= 0) {
            return false;
        }

        if ($type === PaymentComponentType::PRODUCT_ONLY_WORK_ITEM && $refunded >= $allocated) {
            return true;
        }

        return $this->refundedStoreStock->shouldSkip($type, $refId, $refunded, $allocated);
    }
}
