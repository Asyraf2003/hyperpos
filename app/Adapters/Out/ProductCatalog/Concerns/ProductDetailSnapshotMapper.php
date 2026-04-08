<?php

declare(strict_types=1);

namespace App\Adapters\Out\ProductCatalog\Concerns;

trait ProductDetailSnapshotMapper
{
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

    private function initialIdentitySourceLabel(?object $createdVersion, ?object $firstRecordedVersion): string
    {
        if ($createdVersion !== null) {
            return 'created_version';
        }

        if ($firstRecordedVersion !== null) {
            return 'first_recorded_version';
        }

        return 'unavailable';
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
