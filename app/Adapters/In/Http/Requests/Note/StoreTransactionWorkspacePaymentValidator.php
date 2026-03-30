<?php

declare(strict_types=1);

namespace App\Adapters\In\Http\Requests\Note;

use Illuminate\Validation\Validator;

final class StoreTransactionWorkspacePaymentValidator
{
    /**
     * @param mixed $payment
     */
    public static function validate(mixed $payment, Validator $validator): void
    {
        if (! is_array($payment)) {
            $validator->errors()->add('inline_payment', 'Format pembayaran workspace tidak valid.');
            return;
        }

        $decision = (string) ($payment['decision'] ?? 'skip');

        if ($decision === 'skip') {
            return;
        }

        if (! in_array((string) ($payment['payment_method'] ?? ''), ['cash', 'transfer'], true)) {
            $validator->errors()->add('inline_payment.payment_method', 'Metode pembayaran workspace tidak valid.');
        }
        if (! is_string($payment['paid_at'] ?? null) || trim((string) $payment['paid_at']) === '') {
            $validator->errors()->add('inline_payment.paid_at', 'Tanggal bayar wajib diisi.');
        }
        if ($decision === 'pay_partial' && self::intValue($payment['amount_paid_rupiah'] ?? null) <= 0) {
            $validator->errors()->add('inline_payment.amount_paid_rupiah', 'Nominal pembayaran sebagian wajib lebih dari 0.');
        }
        if (($payment['payment_method'] ?? null) === 'cash' && self::intValue($payment['amount_received_rupiah'] ?? null) <= 0) {
            $validator->errors()->add('inline_payment.amount_received_rupiah', 'Uang masuk cash wajib lebih dari 0.');
        }
    }

    private static function intValue(mixed $value): int
    {
        return is_int($value) ? $value : 0;
    }
}
