<?php

declare(strict_types=1);

namespace Tests\Unit\Adapters\In\Http\Requests\Note;

use App\Adapters\In\Http\Requests\Note\StoreTransactionWorkspacePaymentValidator;
use Illuminate\Support\Facades\Validator as ValidatorFacade;
use Tests\TestCase;

final class StoreTransactionWorkspacePaymentValidatorTest extends TestCase
{
    public function test_pay_partial_cash_received_must_cover_explicit_amount_paid(): void
    {
        $payload = [
            'items' => [
                [
                    'entry_mode' => 'service',
                    'description' => null,
                    'part_source' => 'none',
                    'service' => [
                        'name' => 'Servis ADR 0030 Partial',
                        'price_rupiah' => 100000,
                        'notes' => null,
                    ],
                    'product_lines' => [],
                    'external_purchase_lines' => [],
                ],
            ],
            'inline_payment' => [
                'decision' => 'pay_partial',
                'payment_method' => 'cash',
                'paid_at' => date('Y-m-d'),
                'amount_paid_rupiah' => 60000,
                'amount_received_rupiah' => 50000,
                'notes' => null,
            ],
        ];

        $validator = ValidatorFacade::make([], []);

        StoreTransactionWorkspacePaymentValidator::validate($payload, $validator);

        $this->assertTrue(
            $validator->errors()->has('inline_payment.amount_received_rupiah'),
            'Partial cash payment must still require received cash to cover the explicit amount paid.'
        );
    }


    public function test_pay_full_cash_received_is_not_validated_against_payload_grand_total(): void
    {
        $payload = [
            'items' => [
                [
                    'entry_mode' => 'service',
                    'description' => null,
                    'part_source' => 'none',
                    'service' => [
                        'name' => 'Servis ADR 0030',
                        'price_rupiah' => 100000,
                        'notes' => null,
                    ],
                    'product_lines' => [],
                    'external_purchase_lines' => [],
                ],
            ],
            'inline_payment' => [
                'decision' => 'pay_full',
                'payment_method' => 'cash',
                'paid_at' => date('Y-m-d'),
                'amount_paid_rupiah' => null,
                'amount_received_rupiah' => 60000,
                'notes' => null,
            ],
        ];

        $validator = ValidatorFacade::make([], []);

        StoreTransactionWorkspacePaymentValidator::validate($payload, $validator);

        $this->assertFalse(
            $validator->errors()->has('inline_payment.amount_received_rupiah'),
            'Request validator must validate payment payload shape only. Backend settlement/payable logic owns the accepted payable amount. Errors: '
                . $validator->errors()->toJson()
        );
    }
}
