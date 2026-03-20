<?php

declare(strict_types=1);

namespace App\Application\Inventory\UseCases;

use App\Application\Inventory\Services\IssueInventoryOperation;
use App\Application\Shared\DTO\Result;
use App\Core\Shared\Exceptions\DomainException;
use App\Ports\Out\{AuditLogPort, TransactionManagerPort, UuidPort};
use DateTimeImmutable;
use Throwable;

final class RecordStockAdjustmentHandler
{
    public function __construct(
        private readonly IssueInventoryOperation $operation,
        private readonly TransactionManagerPort $transactions,
        private readonly UuidPort $uuid,
        private readonly AuditLogPort $audit,
    ) {
    }

    public function handle(
        string $productId,
        int $qtyIssue,
        string $adjustedAt,
        string $reason,
        string $performedByActorId,
    ): Result {
        $reason = trim($reason);
        $actorId = trim($performedByActorId);
        $started = false;

        try {
            if ($reason === '') {
                return Result::failure(
                    'Alasan stock adjustment wajib diisi.',
                    ['stock_adjustment' => ['AUDIT_REASON_REQUIRED']],
                );
            }

            if ($actorId === '') {
                throw new DomainException('Actor stock adjustment wajib ada.');
            }

            $date = DateTimeImmutable::createFromFormat('!Y-m-d', trim($adjustedAt));

            if ($date === false || $date->format('Y-m-d') !== trim($adjustedAt)) {
                throw new DomainException('Tanggal stock adjustment wajib valid dengan format Y-m-d.');
            }

            $this->transactions->begin();
            $started = true;

            $adjustmentId = $this->uuid->generate();

            $result = $this->operation->execute(
                $productId,
                $qtyIssue,
                $date,
                'stock_adjustment',
                $adjustmentId,
            );

            $this->audit->record('stock_adjustment_recorded', [
                'adjustment_id' => $adjustmentId,
                'product_id' => trim($productId),
                'qty_issue' => $qtyIssue,
                'adjusted_at' => $date->format('Y-m-d'),
                'reason' => $reason,
                'performed_by_actor_id' => $actorId,
                'movement_id' => $result['movement']->id(),
            ]);

            $this->transactions->commit();

            return Result::success([
                'adjustment_id' => $adjustmentId,
                'movement_id' => $result['movement']->id(),
                'qty_on_hand' => $result['product_inventory']->qtyOnHand(),
            ], 'Stock adjustment berhasil dicatat.');
        } catch (DomainException $e) {
            if ($started) {
                $this->transactions->rollBack();
            }

            return Result::failure(
                $e->getMessage(),
                ['stock_adjustment' => ['INVALID_STOCK_ADJUSTMENT']],
            );
        } catch (Throwable $e) {
            if ($started) {
                $this->transactions->rollBack();
            }

            throw $e;
        }
    }
}
