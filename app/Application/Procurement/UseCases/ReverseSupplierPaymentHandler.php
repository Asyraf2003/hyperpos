<?php

declare(strict_types=1);

namespace App\Application\Procurement\UseCases;

use App\Application\Procurement\Services\SupplierInvoiceListProjectionService;
use App\Application\Procurement\Services\SupplierPaymentReversalPreflight;
use App\Application\Procurement\Services\SupplierPaymentReversalRecorder;
use App\Application\Shared\DTO\Result;
use App\Ports\Out\TransactionManagerPort;
use Throwable;

final class ReverseSupplierPaymentHandler
{
    public function __construct(
        private readonly TransactionManagerPort $transactions,
        private readonly SupplierPaymentReversalPreflight $preflight,
        private readonly SupplierPaymentReversalRecorder $recorder,
        private readonly SupplierInvoiceListProjectionService $projection,
    ) {
    }

    public function handle(string $supplierPaymentId, string $reason, string $performedByActorId): Result
    {
        $prepared = $this->preflight->prepare($supplierPaymentId, $reason, $performedByActorId);

        if ($prepared->isFailure()) {
            return $prepared;
        }

        $started = false;

        try {
            $this->transactions->begin();
            $started = true;

            $data = $prepared->data();

            $reversalId = $this->recorder->record(
                (string) $data['payment_id'],
                (string) $data['supplier_invoice_id'],
                (int) $data['amount_rupiah'],
                (string) $data['paid_at'],
                (string) $data['proof_status'],
                (string) $data['reason'],
                (string) $data['actor_id'],
            );

            $this->projection->syncInvoice((string) $data['supplier_invoice_id']);

            $this->transactions->commit();

            return Result::success(
                ['id' => $reversalId],
                'Reversal pembayaran supplier berhasil dicatat.'
            );
        } catch (Throwable $e) {
            if ($started) {
                $this->transactions->rollBack();
            }

            throw $e;
        }
    }
}
