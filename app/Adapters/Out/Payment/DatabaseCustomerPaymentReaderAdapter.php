<?php

declare(strict_types=1);

namespace App\Adapters\Out\Payment;

use App\Core\Payment\CustomerPayment\CustomerPayment;
use App\Core\Shared\Exceptions\DomainException;
use App\Core\Shared\ValueObjects\Money;
use App\Ports\Out\Payment\CustomerPaymentReaderPort;
use DateTimeImmutable;
use Illuminate\Support\Facades\DB;

final class DatabaseCustomerPaymentReaderAdapter implements CustomerPaymentReaderPort
{
    public function getById(string $id): ?CustomerPayment
    {
        $normalizedId = trim($id);

        if ($normalizedId === '') {
            throw new DomainException('Customer payment id wajib ada.');
        }

        $row = DB::table('customer_payments')
            ->where('id', $normalizedId)
            ->first();

        if ($row === null) {
            return null;
        }

        return CustomerPayment::rehydrate(
            (string) $row->id,
            Money::fromInt((int) $row->amount_rupiah),
            $this->parseDate((string) $row->paid_at),
            (string) ($row->payment_method ?? CustomerPayment::METHOD_UNKNOWN),
        );
    }

    public function getTotalAllocatedAmountByPaymentId(string $customerPaymentId): Money
    {
        $normalizedId = trim($customerPaymentId);

        if ($normalizedId === '') {
            throw new DomainException('Customer payment id wajib ada.');
        }

        $componentTotal = (int) DB::table('payment_component_allocations')
            ->where('customer_payment_id', $normalizedId)
            ->sum('allocated_amount_rupiah');

        if ($componentTotal > 0) {
            return Money::fromInt($componentTotal);
        }

        $legacyTotal = (int) DB::table('payment_allocations')
            ->where('customer_payment_id', $normalizedId)
            ->sum('amount_rupiah');

        return Money::fromInt($legacyTotal);
    }

    private function parseDate(string $value): DateTimeImmutable
    {
        $parsed = DateTimeImmutable::createFromFormat('!Y-m-d', trim($value));

        if ($parsed === false || $parsed->format('Y-m-d') !== trim($value)) {
            throw new DomainException('Paid at pada customer payment harus berupa tanggal valid dengan format Y-m-d.');
        }

        return $parsed;
    }
}
