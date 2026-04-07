<?php

declare(strict_types=1);

namespace App\Adapters\Out\ProductCatalog;

use App\Application\ProductCatalog\Context\ProductChangeContext;
use App\Core\ProductCatalog\Product\Product;
use App\Ports\Out\ProductCatalog\ProductLifecyclePort;
use App\Ports\Out\ProductCatalog\ProductWriterPort;
use App\Ports\Out\TransactionManagerPort;
use App\Ports\Out\UuidPort;
use Illuminate\Support\Facades\DB;
use Throwable;

final class DatabaseVersionedProductWriterAdapter implements ProductWriterPort, ProductLifecyclePort
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

            $snapshot = [
                'id' => (string) $row->id,
                'kode_barang' => $row->kode_barang !== null ? (string) $row->kode_barang : null,
                'nama_barang' => (string) $row->nama_barang,
                'merek' => (string) $row->merek,
                'ukuran' => $row->ukuran !== null ? (int) $row->ukuran : null,
                'harga_jual' => (int) $row->harga_jual,
                'deleted_at' => $occurredAt->toDateTimeString(),
                'deleted_by_actor_id' => $actorId,
            ];

            DB::table('product_versions')->insert([
                'id' => $this->uuid->generate(),
                'product_id' => $productId,
                'revision_no' => $revisionNo,
                'event_name' => 'product_soft_deleted',
                'changed_at' => $occurredAt,
                'changed_by_actor_id' => $actorId,
                'change_reason' => null,
                'snapshot_json' => json_encode($snapshot, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE),
            ]);

            DB::table('audit_events')->insert([
                'id' => $this->uuid->generate(),
                'bounded_context' => 'product_catalog',
                'aggregate_type' => 'product',
                'aggregate_id' => $productId,
                'event_name' => 'product_soft_deleted',
                'occurred_at' => $occurredAt,
                'actor_id' => $actorId,
                'actor_role' => null,
                'reason' => null,
                'source_channel' => 'web_admin',
                'request_id' => null,
                'correlation_id' => null,
                'metadata_json' => json_encode([
                    'product' => $snapshot,
                    'revision_no' => $revisionNo,
                ], JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE),
            ]);

            $this->transactions->commit();

            return true;
        } catch (Throwable $e) {
            if ($started) {
                $this->transactions->rollBack();
            }

            throw $e;
        }
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
            'nama_barang_normalized' => $this->normalizeForSearch($product->namaBarang()),
            'merek' => $product->merek(),
            'merek_normalized' => $this->normalizeForSearch($product->merek()),
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

    private function normalizeForSearch(string $value): string
    {
        $normalized = preg_replace('/\s+/', ' ', trim($value)) ?? trim($value);

        return mb_strtolower($normalized);
    }
}
