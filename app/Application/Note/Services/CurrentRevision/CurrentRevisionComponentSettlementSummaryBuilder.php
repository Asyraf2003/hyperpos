<?php

declare(strict_types=1);

namespace App\Application\Note\Services\CurrentRevision;

use App\Application\Note\Services\NoteBillingProjectionRefundedStoreStockComponentSkipper;
use App\Application\Note\Services\NoteOperationalSettlementLabelResolver;
use App\Core\Note\Revision\NoteRevisionLineSnapshot;
use App\Core\Payment\PaymentComponentAllocation\PaymentComponentType;

final class CurrentRevisionComponentSettlementSummaryBuilder
{
    public function __construct(
        private readonly NoteOperationalSettlementLabelResolver $labels,
        private readonly CurrentRevisionLineBillingComponentMapper $components,
        private readonly NoteBillingProjectionRefundedStoreStockComponentSkipper $refundedStoreStock,
    ) {
    }

    /**
     * @param list<NoteRevisionLineSnapshot> $lines
     * @param array<string, int> $paymentTotals
     * @param array<string, int> $refundTotals
     * @param array<string, int> $componentPaymentTotals
     * @param array<string, int> $componentRefundTotals
     * @return array<string, array<string, int|string>>
     */
    public function build(
        array $lines,
        array $paymentTotals,
        array $refundTotals,
        array $componentPaymentTotals,
        array $componentRefundTotals,
    ): array {
        $summary = [];

        foreach ($lines as $line) {
            $key = $line->workItemRootId() ?? $line->id();
            $subtotal = $line->subtotalRupiah();
            $allocated = (int) ($paymentTotals[$key] ?? 0);
            $refunded = (int) ($refundTotals[$key] ?? 0);
            $componentSettlement = $this->componentSettlement(
                $line,
                $componentPaymentTotals,
                $componentRefundTotals,
            );
            $netPaid = $componentSettlement['net_paid_rupiah'];
            $outstanding = $componentSettlement['outstanding_rupiah'];

            $summary[$key] = [
                'allocated_rupiah' => $allocated,
                'refunded_rupiah' => $refunded,
                'net_paid_rupiah' => $netPaid,
                'outstanding_rupiah' => $outstanding,
                'settlement_label' => $outstanding <= 0
                    ? 'lunas'
                    : $this->labels->resolve($subtotal, $netPaid),
            ];
        }

        return $summary;
    }

    /**
     * @param array<string, int> $componentPaymentTotals
     * @param array<string, int> $componentRefundTotals
     * @return array{net_paid_rupiah:int,outstanding_rupiah:int}
     */
    private function componentSettlement(
        NoteRevisionLineSnapshot $line,
        array $componentPaymentTotals,
        array $componentRefundTotals,
    ): array {
        $netPaid = 0;
        $outstanding = 0;

        foreach ($this->components->map($line, $line->payload()) as $component) {
            $type = (string) ($component['component_type'] ?? '');
            $refId = (string) ($component['component_ref_id'] ?? '');
            $total = (int) ($component['component_total_rupiah'] ?? 0);

            if ($type === '' || $refId === '' || $total <= 0) {
                continue;
            }

            $key = $type . '::' . $refId;
            $allocated = min((int) ($componentPaymentTotals[$key] ?? 0), $total);
            $refunded = min((int) ($componentRefundTotals[$key] ?? 0), $allocated);

            if ($this->isNonCollectibleRefundedProduct($type, $refId, $allocated, $refunded)) {
                continue;
            }

            $componentNetPaid = max($allocated - $refunded, 0);
            $netPaid += $componentNetPaid;
            $outstanding += max($total - $componentNetPaid, 0);
        }

        return [
            'net_paid_rupiah' => $netPaid,
            'outstanding_rupiah' => $outstanding,
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
