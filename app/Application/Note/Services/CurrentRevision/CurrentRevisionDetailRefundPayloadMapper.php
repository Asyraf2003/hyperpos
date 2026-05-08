<?php

declare(strict_types=1);

namespace App\Application\Note\Services\CurrentRevision;

use App\Application\Note\Services\RefundImpactPayloadBuilder;

final class CurrentRevisionDetailRefundPayloadMapper
{
    public function __construct(
        private readonly RefundImpactPayloadBuilder $refundImpact,
        private readonly CurrentRevisionLinePresentationSupport $presentation,
    ) {
    }

    /**
     * @param array<string, mixed> $payload
     * @return array<string, mixed>
     */
    public function map(array $payload, int $netPaid): array
    {
        $impact = $this->refundImpact->fromRevisionPayload($payload, $netPaid);
        $summary = is_array($impact['effect_summary'] ?? null) ? $impact['effect_summary'] : [];
        $storeReturnCount = (int) ($summary['stock_store_return_count'] ?? 0);
        $externalCount = (int) ($summary['external_item_count'] ?? 0);

        return [
            'refund_stock_return_count' => $storeReturnCount,
            'refund_external_count' => $externalCount,
            'refund_money_possible' => $netPaid > 0,
            'refund_preview_label' => $this->presentation->refundPreviewLabel($storeReturnCount, $externalCount),
            'refund_impact' => $impact,
        ];
    }
}
