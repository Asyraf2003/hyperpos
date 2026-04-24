<?php

declare(strict_types=1);

namespace App\Application\Note\Services\CurrentRevision;

use App\Application\Note\Services\RefundImpactPayloadBuilder;
use App\Application\Note\Services\WorkItemOperationalStatusResolver;
use App\Core\Note\Revision\NoteRevisionLineSnapshot;

final class CurrentRevisionDetailRowMapper
{
    public function __construct(
        private readonly WorkItemOperationalStatusResolver $statuses,
        private readonly CurrentRevisionLinePresentationSupport $presentation,
        private readonly RefundImpactPayloadBuilder $refundImpact,
    ) {
    }

    /**
     * @param list<NoteRevisionLineSnapshot> $lines
     * @param array<string, array<string, int|string>> $settlements
     * @return list<array<string, mixed>>
     */
    public function map(array $lines, array $settlements): array
    {
        return array_map(fn (NoteRevisionLineSnapshot $line): array => $this->mapLine($line, $settlements), $lines);
    }

    /**
     * @param array<string, array<string, int|string>> $settlements
     * @return array<string, mixed>
     */
    private function mapLine(NoteRevisionLineSnapshot $line, array $settlements): array
    {
        $key = $line->workItemRootId() ?? $line->id();
        $settlement = $settlements[$key] ?? [
            'allocated_rupiah' => 0,
            'refunded_rupiah' => 0,
            'net_paid_rupiah' => 0,
            'outstanding_rupiah' => $line->subtotalRupiah(),
            'settlement_label' => 'hutang',
        ];

        $payload = $line->payload();
        $storeLineCount = count(is_array($payload['store_stock_lines'] ?? null) ? $payload['store_stock_lines'] : []);
        $externalLineCount = count(is_array($payload['external_purchase_lines'] ?? null) ? $payload['external_purchase_lines'] : []);
        $refunded = (int) ($settlement['refunded_rupiah'] ?? 0);
        $netPaid = (int) ($settlement['net_paid_rupiah'] ?? 0);
        $outstanding = (int) ($settlement['outstanding_rupiah'] ?? $line->subtotalRupiah());
        $lineStatus = $this->statuses->resolve($outstanding, $refunded);
        $refundImpact = $this->refundImpact->fromRevisionPayload($payload, $netPaid);
        $summary = is_array($refundImpact['effect_summary'] ?? null) ? $refundImpact['effect_summary'] : [];

        $refundStockReturnCount = (int) ($summary['stock_store_return_count'] ?? 0);
        $refundExternalCount = (int) ($summary['external_item_count'] ?? 0);

        $lineLabel = $line->serviceLabel();
        if ($lineLabel === null || trim($lineLabel) === '') {
            $lineLabel = $storeLineCount > 0 ? ('Produk x' . $storeLineCount) : 'Line Nota';
        }

        return [
            'id' => $key,
            'line_no' => $line->lineNo(),
            'line_label' => $lineLabel,
            'type_label' => $this->presentation->typeLabel($line->transactionType()),
            'transaction_type' => $line->transactionType(),
            'can_correct_service_only' => $line->transactionType() === 'service_only',
            'status' => $line->status(),
            'settlement_label' => (string) ($settlement['settlement_label'] ?? 'hutang'),
            'subtotal_rupiah' => $line->subtotalRupiah(),
            'allocated_rupiah' => (int) ($settlement['allocated_rupiah'] ?? 0),
            'refunded_rupiah' => $refunded,
            'net_paid_rupiah' => $netPaid,
            'outstanding_rupiah' => $outstanding,
            'has_service_component' => $line->serviceLabel() !== null,
            'store_stock_count' => $storeLineCount,
            'external_purchase_count' => $externalLineCount,
            'refund_stock_return_count' => $refundStockReturnCount,
            'refund_external_count' => $refundExternalCount,
            'refund_money_possible' => $netPaid > 0,
            'refund_preview_label' => $this->presentation->refundPreviewLabel($refundStockReturnCount, $refundExternalCount),
            'refund_impact' => $refundImpact,
            'line_status' => $lineStatus,
            'can_edit' => $lineStatus === WorkItemOperationalStatusResolver::STATUS_OPEN,
            'can_pay' => $lineStatus === WorkItemOperationalStatusResolver::STATUS_OPEN,
            'can_refund' => in_array($lineStatus, [
                WorkItemOperationalStatusResolver::STATUS_OPEN,
                WorkItemOperationalStatusResolver::STATUS_CLOSE,
            ], true),
            'can_view_detail' => true,
        ];
    }
}
