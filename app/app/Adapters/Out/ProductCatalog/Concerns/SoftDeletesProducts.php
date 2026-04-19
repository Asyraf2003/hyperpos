<?php

declare(strict_types=1);

namespace App\Adapters\Out\ProductCatalog\Concerns;

use Illuminate\Support\Facades\DB;
use Throwable;

trait SoftDeletesProducts
{
    public function softDelete(string $productId, ?string $actorId): bool
    {
        $started = false;

        try {
            $this->transactions->begin();
            $started = true;

            $row = DB::table('products')
                ->where('id', $productId)
                ->whereNull('deleted_at')
                ->first([
                    'id',
                    'kode_barang',
                    'nama_barang',
                    'merek',
                    'ukuran',
                    'harga_jual',
                    'reorder_point_qty',
                    'critical_threshold_qty',
                ]);

            if ($row === null) {
                $this->transactions->rollBack();

                return false;
            }

            $occurredAt = now();

            DB::table('products')
                ->where('id', $productId)
                ->update([
                    'deleted_at' => $occurredAt,
                    'deleted_by_actor_id' => $actorId,
                ]);

            $revisionNo = $this->nextRevisionNo($productId);
            $snapshot = $this->toDeletedSnapshot(
                $row,
                $occurredAt->toDateTimeString(),
                $actorId,
            );

            $this->recordProductVersion(
                $productId,
                $revisionNo,
                'product_soft_deleted',
                $occurredAt,
                $actorId,
                null,
                $snapshot,
            );

            $this->recordProductAuditEvent(
                $productId,
                $revisionNo,
                'product_soft_deleted',
                $occurredAt,
                $actorId,
                null,
                null,
                'web_admin',
                $snapshot,
            );

            $this->transactions->commit();

            return true;
        } catch (Throwable $e) {
            if ($started) {
                $this->transactions->rollBack();
            }

            throw $e;
        }
    }
}
