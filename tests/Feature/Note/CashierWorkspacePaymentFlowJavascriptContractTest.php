<?php

declare(strict_types=1);

namespace Tests\Feature\Note;

use Tests\TestCase;

final class CashierWorkspacePaymentFlowJavascriptContractTest extends TestCase
{
    public function test_payment_flow_consumes_backend_payable_dataset_for_edit_calculator(): void
    {
        $script = (string) file_get_contents(base_path('public/assets/static/js/pages/cashier-note-workspace/payment-flow.js'));

        $this->assertStringContainsString(
            'dataset.backendPayableRupiah',
            $script,
            'Payment flow JS must read the backend payable dataset exposed by the edit payment modal.'
        );

        $this->assertStringContainsString(
            'dataset.backendPaymentBasis',
            $script,
            'Payment flow JS must read the backend payment basis dataset exposed by the edit payment modal.'
        );

        $this->assertStringContainsString(
            'backend_outstanding_settlement',
            $script,
            'Payment flow JS must only treat backend_outstanding_settlement as backend-owned payable truth.'
        );
    }
    public function test_payment_flow_keeps_backend_settlement_context_when_initial_payable_is_zero(): void
    {
        $script = file_get_contents(base_path('public/assets/static/js/pages/cashier-note-workspace/payment-flow.js'));

        self::assertIsString($script);
        self::assertStringContainsString('dataset.backendNetPaidRupiah', $script);
        self::assertStringContainsString('dataset.backendGrossTotalRupiah', $script);
        self::assertStringContainsString('modal.dataset.backendPaymentBasis !== "backend_outstanding_settlement"', $script);
        self::assertStringContainsString('return Math.max(total - context.netPaid, 0);', $script);
        self::assertStringNotContainsString('return backendPayable > 0 ? backendPayable : total;', $script);
    }

    public function test_payment_flow_blocks_unavailable_payment_choices_before_submit(): void
    {
        $script = file_get_contents(base_path('public/assets/static/js/pages/cashier-note-workspace/payment-flow.js'));

        self::assertIsString($script);
        self::assertStringContainsString('const paymentChoiceDisabled = (choice, total) => {', $script);
        self::assertStringContainsString('return baseInvalid || effectivePaymentTotal(total) <= 0;', $script);
        self::assertStringContainsString('button.disabled = disabled;', $script);
        self::assertStringContainsString('choiceButton.disabled', $script);
        self::assertStringContainsString('const resetUnavailablePaymentMode = (total) => {', $script);
        self::assertStringContainsString('Tambahkan minimal satu rincian nota sebelum proses nota.', $script);
        self::assertStringContainsString('Total nota harus lebih besar dari 0 sebelum proses nota.', $script);
    }


}
