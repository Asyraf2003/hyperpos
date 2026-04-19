<?php

declare(strict_types=1);

namespace App\Application\Procurement\Services;

use App\Application\Shared\DTO\Result;
use App\Core\Shared\Exceptions\DomainException;
use Illuminate\Support\Facades\DB;
use DateTimeImmutable;

final class SupplierReceiptReversalPreflight
{
    public function prepare(string $supplierReceiptId, string $reversedAt, string $actorId): Result
    {
        $actorId = trim($actorId);

        if ($actorId === '') {
            throw new DomainException('Actor reversal penerimaan supplier wajib ada.');
        }

        $date = DateTimeImmutable::createFromFormat('!Y-m-d', trim($reversedAt));

        if ($date === false || $date->format('Y-m-d') !== trim($reversedAt)) {
            throw new DomainException('Tanggal reversal penerimaan supplier wajib valid dengan format Y-m-d.');
        }

        $receipt = DB::table('supplier_receipts')
            ->where('id', trim($supplierReceiptId))
            ->first(['id', 'supplier_invoice_id']);

        if ($receipt === null) {
            return Result::failure(
                'Penerimaan supplier tidak ditemukan.',
                ['supplier_receipt_reversal' => ['SUPPLIER_RECEIPT_NOT_FOUND']]
            );
        }

        $alreadyReversed = DB::table('supplier_receipt_reversals')
            ->where('supplier_receipt_id', trim($supplierReceiptId))
            ->exists();

        if ($alreadyReversed) {
            return Result::failure(
                'Penerimaan supplier ini sudah direverse.',
                ['supplier_receipt_reversal' => ['SUPPLIER_RECEIPT_ALREADY_REVERSED']]
            );
        }

        return Result::success([
            'supplier_receipt_id' => (string) $receipt->id,
            'supplier_invoice_id' => (string) $receipt->supplier_invoice_id,
            'reversed_at' => $date,
            'actor_id' => $actorId,
        ]);
    }
}
