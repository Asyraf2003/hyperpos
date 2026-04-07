<?php

declare(strict_types=1);

namespace App\Adapters\Out\ProductCatalog;

use App\Application\ProductCatalog\Context\ProductChangeContext;
use App\Core\ProductCatalog\Product\Product;
use App\Ports\Out\ProductCatalog\ProductWriterPort;
use App\Ports\Out\TransactionManagerPort;
use App\Ports\Out\UuidPort;
use Illuminate\Support\Facades\DB;
use Throwable;

final class DatabaseVersionedProductWriterAdapter implements ProductWriterPort
{
    public function __construct(
        private readonly TransactionManagerPort $transactions,
        private readonly UuidPort $uuid,
        private readonly ProductChangeContext $changeContext,
    ) {
    }

    public function create(Product $product): void
    {
        $this->persist($product, 'product_created', true);
    }

    public function update(Product $product): void
    {
        $this->persist($product, 'product_updated', false);
    }

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

            DB::table('product_versions')->insert([
                'id' => $this->uuid->generate(),
                'product_id' => $product->id(),
                'revision_no' => $revisionNo,
                'event_name' => $eventName,
                'changed_at' => $occurredAt,
                'changed_by_actor_id' => $context['actor_id'],
                'change_reason' => $context['reason'],
                'snapshot_json' => json_encode($this->toSnapshot($product), JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE),
            ]);

            DB::table('audit_events')->insert([
                'id' => $this->uuid->generate(),
                'bounded_context' => 'product_catalog',
                'aggregate_type' => 'product',
                'aggregate_id' => $product->id(),
                'event_name' => $eventName,
                'occurred_at' => $occurredAt,
                'actor_id' => $context['actor_id'],
                'actor_role' => $context['actor_role'],
                'reason' => $context['reason'],
                'source_channel' => $context['source_channel'],
                'request_id' => null,
                'correlation_id' => null,
                'metadata_json' => json_encode([
                    'product' => $this->toSnapshot($product),
                    'revision_no' => $revisionNo,
                ], JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE),
            ]);

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

    /**
     * @return array<string, string|int|null>
     */
    private function toProductRecord(Product $product): array
    {
        return [
            'id' => $product->id(),
            'kode_barang' => $product->kodeBarang(),
            'nama_barang' => $product->namaBarang(),
            'merek' => $product->merek(),
            'ukuran' => $product->ukuran(),
            'harga_jual' => $product->hargaJual()->amount(),
        ];
    }

    /**
     * @return array{
     *     id:string,
     *     kode_barang:?string,
     *     nama_barang:string,
     *     merek:string,
     *     ukuran:?int,
     *     harga_jual:int
     * }
     */
    private function toSnapshot(Product $product): array
    {
        return [
            'id' => $product->id(),
            'kode_barang' => $product->kodeBarang(),
            'nama_barang' => $product->namaBarang(),
            'merek' => $product->merek(),
            'ukuran' => $product->ukuran(),
            'harga_jual' => $product->hargaJual()->amount(),
        ];
    }

    private function nextRevisionNo(string $productId): int
    {
        $current = DB::table('product_versions')
            ->where('product_id', $productId)
            ->max('revision_no');

        return ((int) ($current ?? 0)) + 1;
    }
}
