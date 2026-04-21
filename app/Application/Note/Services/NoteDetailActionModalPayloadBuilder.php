<?php

declare(strict_types=1);

namespace App\Application\Note\Services;

final class NoteDetailActionModalPayloadBuilder
{
    /**
     * @param list<array<string, mixed>> $rows
     * @param array<string, mixed> $operational
     * @param array<string, mixed> $lineSummary
     * @param list<array<string, mixed>> $refundPaymentOptions
     * @return array<string, mixed>
     */
    public function build(
        bool $isOpen,
        array $rows,
        array $operational,
        array $lineSummary,
        array $refundPaymentOptions,
    ): array {
        $paymentRows = $this->filterRows($rows, 'can_pay');
        $refundRows = $this->filterRows($rows, 'can_refund');

        $canShowPaymentAction = $isOpen
            && (int) ($lineSummary['open_count'] ?? 0) > 0
            && (int) ($operational['outstanding_rupiah'] ?? 0) > 0
            && $paymentRows !== [];

        $canShowRefundAction = $refundRows !== [] && $refundPaymentOptions !== [];

        return [
            'can_show_payment_form' => $canShowPaymentAction,
            'can_show_refund_form' => (int) ($lineSummary['close_count'] ?? 0) > 0,
            'can_show_payment_action' => $canShowPaymentAction,
            'can_show_refund_action' => $canShowRefundAction,
            'payment_rows' => $paymentRows,
            'refund_rows' => $refundRows,
            'refund_payment_options' => $refundPaymentOptions,
            'detail_action_contract' => [
                'selection_mode' => 'modal_only',
                'payment_flow' => 'launcher_then_modal_selection',
                'refund_flow' => 'launcher_then_modal_selection',
            ],
        ];
    }

    /**
     * @param list<array<string, mixed>> $rows
     * @return list<array<string, mixed>>
     */
    private function filterRows(array $rows, string $eligibilityKey): array
    {
        return array_values(array_filter(
            $rows,
            static fn (array $row): bool => (bool) ($row[$eligibilityKey] ?? false)
        ));
    }
}
