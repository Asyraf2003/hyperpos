<?php

declare(strict_types=1);

namespace App\Application\Procurement\Services;

use App\Application\Shared\DTO\Result;
use App\Core\Shared\Exceptions\DomainException;
use App\Ports\Out\Procurement\SupplierReceiptReversalWriterPort;
use DateTimeImmutable;

final class SupplierReceiptReversalPreflight
{
    public function __construct(
        private readonly SupplierReceiptReversalWriterPort $reversals,
    ) {
    }

    public function prepare(string $supplierReceiptId, string $reversedAt, string $actorId): Result
    {
        $receiptId = trim($supplierReceiptId);
        $actorId = trim($actorId);

        if ($actorId === '') {
            throw new DomainException('Actor reversal penerimaan supplier wajib ada.');
        }

        $date = DateTimeImmutable::createFromFormat('!Y-m-d', trim($reversedAt));

        if ($date === false || $date->format('Y-m-d') !== trim($reversedAt)) {
            throw new DomainException('Tanggal reversal penerimaan supplier wajib valid dengan format Y-m-d.');
        }

        $receipt = $this->reversals->findReceiptSnapshotForReversal($receiptId);

        if ($receipt === null) {
            return Result::failure(
                'Penerimaan supplier tidak ditemukan.',
                ['supplier_receipt_reversal' => ['SUPPLIER_RECEIPT_NOT_FOUND']]
            );
        }

        if ($this->reversals->receiptAlreadyReversed($receiptId)) {
            return Result::failure(
                'Penerimaan supplier ini sudah direverse.',
                ['supplier_receipt_reversal' => ['SUPPLIER_RECEIPT_ALREADY_REVERSED']]
            );
        }

        return Result::success([
            'supplier_receipt_id' => $receipt['supplier_receipt_id'],
            'supplier_invoice_id' => $receipt['supplier_invoice_id'],
            'reversed_at' => $date,
            'actor_id' => $actorId,
        ]);
    }
}
