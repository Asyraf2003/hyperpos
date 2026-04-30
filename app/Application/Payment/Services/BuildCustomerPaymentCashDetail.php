<?php

declare(strict_types=1);

namespace App\Application\Payment\Services;

use App\Core\Payment\CustomerPayment\CustomerPayment;
use App\Core\Payment\CustomerPayment\CustomerPaymentCashDetail;
use App\Core\Shared\Exceptions\DomainException;
use App\Core\Shared\ValueObjects\Money;

final class BuildCustomerPaymentCashDetail
{
    public function execute(
        CustomerPayment $payment,
        Money $amount,
        ?int $amountReceivedRupiah,
    ): ?CustomerPaymentCashDetail {
        if ($payment->paymentMethod() !== CustomerPayment::METHOD_CASH) {
            return null;
        }

        if ($amountReceivedRupiah === null) {
            throw new DomainException('Uang masuk wajib diisi untuk pembayaran cash.');
        }

        return CustomerPaymentCashDetail::create(
            $payment->id(),
            $amount,
            Money::fromInt($amountReceivedRupiah),
        );
    }
}
