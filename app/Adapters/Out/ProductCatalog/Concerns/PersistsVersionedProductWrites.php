<?php

declare(strict_types=1);

namespace App\Adapters\Out\ProductCatalog\Concerns;

use App\Core\ProductCatalog\Product\Product;
use Illuminate\Support\Facades\DB;
use Throwable;

trait PersistsVersionedProductWrites
{
    private function persist(Product $product, string $eventName, bool $isCreate): void
    {
        $context = $this->changeContext->snapshot();
        $started = false;

        try {
            $this->transactions->begin();
            $started = true;

            if ($isCreate) {
                DB::table('products')->insert($this->toProductRecord($product));
            } else {
                DB::table('products')
                    ->where('id', $product->id())
                    ->update($this->toProductRecord($product));
            }

            $revisionNo = $this->nextRevisionNo($product->id());
            $occurredAt = now();
            $snapshot = $this->toSnapshot($product);

            $this->recordProductVersion(
                $product->id(),
                $revisionNo,
                $eventName,
                $occurredAt,
                $context['actor_id'],
                $context['reason'],
                $snapshot,
            );

            $this->recordProductAuditEvent(
                $product->id(),
                $revisionNo,
                $eventName,
                $occurredAt,
                $context['actor_id'],
                $context['actor_role'],
                $context['reason'],
                $context['source_channel'],
                $snapshot,
            );

            $this->transactions->commit();
        } catch (Throwable $e) {
            if ($started) {
                $this->transactions->rollBack();
            }

            throw $e;
        } finally {
            $this->changeContext->clear();
        }
    }
}
