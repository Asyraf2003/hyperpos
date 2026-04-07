<?php

declare(strict_types=1);

namespace App\Adapters\Out\ProductCatalog;

use App\Ports\Out\ProductCatalog\ProductDetailReaderPort;
use Illuminate\Support\Facades\DB;

final class DatabaseProductDetailReaderAdapter implements ProductDetailReaderPort
{
    public function getDetail(string $productId): ?array
    {
        $product = DB::table('products')
            ->where('id', $productId)
            ->first(['id', 'kode_barang', 'nama_barang', 'merek', 'ukuran', 'harga_jual']);

        if ($product === null) {
            return null;
        }

        $initialVersion = DB::table('product_versions')
            ->where('product_id', $productId)
            ->orderBy('revision_no')
            ->first(['changed_at', 'snapshot_json']);

        $current = $this->mapCurrentProduct($product);
        $initial = $initialVersion === null ? null : $this->mapInitialIdentity($initialVersion);

        return [
            'product' => $current,
            'initial_identity' => $initial,
            'has_identity_changes' => $initial !== null && $this->identityChanged($current, $initial),
        ];
    }

    public function getVersionTimeline(string $productId): array
    {
        return DB::table('product_versions')
            ->where('product_id', $productId)
            ->orderByDesc('revision_no')
            ->get(['revision_no', 'event_name', 'changed_at', 'changed_by_actor_id', 'change_reason', 'snapshot_json'])
            ->map(fn (object $row): array => [
                'revision_no' => (int) $row->revision_no,
                'event_name' => (string) $row->event_name,
                'changed_at' => (string) $row->changed_at,
                'changed_by_actor_id' => $row->changed_by_actor_id !== null ? (string) $row->changed_by_actor_id : null,
                'change_reason' => $row->change_reason !== null ? (string) $row->change_reason : null,
                'snapshot' => $this->decodeSnapshot((string) $row->snapshot_json),
            ])
            ->all();
    }

    private function mapCurrentProduct(object $product): array
    {
        return [
            'id' => (string) $product->id,
            'kode_barang' => $product->kode_barang !== null ? (string) $product->kode_barang : null,
            'nama_barang' => (string) $product->nama_barang,
            'merek' => (string) $product->merek,
            'ukuran' => $product->ukuran !== null ? (int) $product->ukuran : null,
            'harga_jual' => (int) $product->harga_jual,
        ];
    }

    private function mapInitialIdentity(object $version): array
    {
        $snapshot = $this->decodeSnapshot((string) $version->snapshot_json);

        return [
            'kode_barang' => $snapshot['kode_barang'] ?? null,
            'nama_barang' => (string) ($snapshot['nama_barang'] ?? ''),
            'merek' => (string) ($snapshot['merek'] ?? ''),
            'ukuran' => isset($snapshot['ukuran']) ? (int) $snapshot['ukuran'] : null,
            'harga_jual' => isset($snapshot['harga_jual']) ? (int) $snapshot['harga_jual'] : 0,
            'changed_at' => (string) $version->changed_at,
        ];
    }

    private function identityChanged(array $current, array $initial): bool
    {
        foreach (['kode_barang', 'nama_barang', 'merek', 'ukuran', 'harga_jual'] as $key) {
            if (($current[$key] ?? null) !== ($initial[$key] ?? null)) {
                return true;
            }
        }

        return false;
    }

    private function decodeSnapshot(string $snapshotJson): array
    {
        $decoded = json_decode($snapshotJson, true, 512, JSON_THROW_ON_ERROR);

        return is_array($decoded) ? $decoded : [];
    }
}
