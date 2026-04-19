<?php

declare(strict_types=1);

namespace App\Adapters\Out\ProductCatalog\Concerns;

use Illuminate\Support\Facades\DB;
use Throwable;

trait RestoresProducts
{
    public function restore(string $productId, ?string $actorId): bool
    {
        $started = false;

        try {
            $this->transactions->begin();
            $started = true;

            $row = DB::table('products')
                ->where('id', $productId)
                ->whereNotNull('deleted_at')
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
                    'deleted_at' => null,
                    'deleted_by_actor_id' => null,
                    'delete_reason' => null,
                ]);

            $revisionNo = $this->nextRevisionNo($productId);
            $snapshot = $this->toRestoredSnapshot($row);

            $this->recordProductVersion(
                $productId,
                $revisionNo,
                'product_restored',
                $occurredAt,
                $actorId,
                null,
                $snapshot,
            );

            $this->recordProductAuditEvent(
                $productId,
                $revisionNo,
                'product_restored',
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
